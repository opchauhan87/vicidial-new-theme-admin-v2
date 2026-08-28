#!/usr/bin/env python3

import socket
import struct
import wave
import time
import os
import socket
import audioop
import math
import json
import urllib.request
import tempfile

from faster_whisper import WhisperModel

HOST = "0.0.0.0"
PORT = 9019

OLLAMA_URL = "http://127.0.0.1:11434/api/chat"
OLLAMA_MODEL = "llama3.2:3b"

PIPER_MODEL = "models/piper/en_US-lessac-medium.onnx"

RATE_IN = 8000
RATE_TTS = 22050
RATE_OUT = 8000

WIDTH = 2
CHANNELS = 1

FRAME_MS = 20
FRAME_BYTES = RATE_IN * WIDTH * FRAME_MS // 1000

SPEECH_RMS_THRESHOLD = 600
SPEECH_START_FRAMES = 3
MIN_SPEECH_SECONDS = 0.60
MAX_SPEECH_SECONDS = 7.0
END_SILENCE_SECONDS = 0.70

MIN_SPEECH_BYTES = int(
    RATE_IN * WIDTH * MIN_SPEECH_SECONDS
)

MAX_SPEECH_BYTES = int(
    RATE_IN * WIDTH * MAX_SPEECH_SECONDS
)

END_SILENCE_FRAMES = int(
    END_SILENCE_SECONDS * 1000 / FRAME_MS
)


print("HNC Voice AI - FULL VOICE AI")
print("=" * 50)
print("AudioSocket :", "%s:%d" % (HOST, PORT))
print("STT model   : small")
print("STT device  : CPU")
print("STT compute : int8")
print("AI model    :", OLLAMA_MODEL)
print("TTS model   :", PIPER_MODEL)
print("TTS output  : 8 kHz / 16-bit / mono")
print("=" * 50)


print()
print("Loading Whisper model...")

start = time.time()

whisper_model = WhisperModel(
    "small",
    device="cpu",
    compute_type="int8",
    cpu_threads=4,
    num_workers=1
)

print(
    "Whisper loaded in %.2f seconds"
    % (time.time() - start)
)


if not os.path.exists(PIPER_MODEL):

    print()
    print("ERROR: Piper model not found:")
    print(PIPER_MODEL)
    raise SystemExit(1)


def rms_level(pcm):

    if not pcm:
        return 0

    count = len(pcm) // 2

    if count <= 0:
        return 0

    total = 0

    for i in range(0, len(pcm), 2):

        sample = int.from_bytes(
            pcm[i:i + 2],
            byteorder="little",
            signed=True
        )

        total += sample * sample

    return math.sqrt(total / count)


def get_customer_context(phone_number):
    """
    Read customer data from the VICIdial database
    using the caller phone number.

    For the current test, address1 is intentionally used as
    the outstanding amount field.
    Currency is fixed to MYR/RM for this test.
    """

    import subprocess

    phone_number = str(phone_number or "").strip()

    if not phone_number:
        return {
            "lead_id": None,
            "phone_number": "",
            "name": "",
            "balance": None,
            "currency": "MYR"
        }

    # Keep only digits for a safe phone-number lookup.
    phone_digits = "".join(
        ch for ch in phone_number
        if ch.isdigit()
    )

    if not phone_digits:
        print(
            "DATABASE: invalid phone number:",
            phone_number
        )

        return {
            "lead_id": None,
            "phone_number": phone_number,
            "name": "",
            "balance": None,
            "currency": "MYR"
        }

    query = (
        "SELECT lead_id, first_name, last_name, address1 "
        "FROM vicidial_list "
        "WHERE phone_number = '%s' "
        "LIMIT 1"
        % phone_digits
    )

    try:
        result = subprocess.run(
            [
                "mysql",
                "-N",
                "-B",
                "asterisk",
                "-e",
                query
            ],
            capture_output=True,
            text=True,
            timeout=5,
            check=True
        )

        row = result.stdout.strip().split("\t")

        if len(row) < 4:
            print(
                "DATABASE: phone %s not found"
                % phone_digits
            )

            return {
                "lead_id": None,
                "phone_number": phone_digits,
                "name": "",
                "balance": None,
                "currency": "MYR"
            }

        lead_id = row[0].strip()
        first_name = row[1].strip()
        last_name = row[2].strip()
        balance = row[3].strip()

        name = (
            (first_name + " " + last_name).strip()
        )

        print(
            "DATABASE: phone=%s | lead_id=%s | "
            "name=%s | balance=%s RM"
            % (
                phone_digits,
                lead_id,
                name,
                balance
            )
        )

        return {
            "lead_id": lead_id,
            "phone_number": phone_digits,
            "name": name,
            "balance": balance,
            "currency": "MYR"
        }

    except Exception as e:

        print(
            "DATABASE ERROR:",
            repr(e)
        )

        return {
            "lead_id": None,
            "phone_number": phone_digits,
            "name": "",
            "balance": None,
            "currency": "MYR"
        }


def get_phone_from_uuid(call_uuid):
    """
    Resolve the caller phone number from the
    Asterisk UUID mapping file.
    """

    if not call_uuid:
        return ""

    path = (
        "/run/hnc-ai/"
        + call_uuid
        + ".phone"
    )

    try:

        with open(path, "r") as f:
            phone = f.read().strip()

        phone = "".join(
            ch for ch in phone
            if ch.isdigit()
        )

        if phone:
            print(
                "CALLER PHONE: %s"
                % phone
            )

        else:
            print(
                "CALLER PHONE: [empty]"
            )

        return phone

    except Exception as e:

        print(
            "CALLER PHONE ERROR:",
            repr(e)
        )

        return ""



def detect_customer_intent(transcript):
    """
    Detect common customer intents before sending the
    conversation to the small language model.
    """

    text = " ".join(
        transcript.lower().strip().split()
    )

    if not text:
        return "unknown"

    # Balance / outstanding amount.
    balance_words = (
        "balance",
        "outstanding",
        "how much do i owe",
        "how much is owed",
        "amount due",
        "amount outstanding",
        "how much money"
    )

    if any(word in text for word in balance_words):
        return "balance"

    # Already paid.
    #
    # Keep this intentionally specific so that a normal
    # payment request such as "I want to pay" does not
    # get classified as already_paid.
    paid_words = (
        "already paid",
        "i already paid",
        "i paid",
        "already cleared",
        "i already cleared",
        "payment was made",
        "payment has been made",
        "already made the payment",
        "already made payment",
        "paid this",
        "paid the amount",
        "payment already made"
    )

    if any(phrase in text for phrase in paid_words):
        return "already_paid"

    # Common STT variations where the words are separated.
    if (
        "already" in text
        and (
            "paid" in text
            or "cleared" in text
        )
    ):
        return "already_paid"

    if (
        "payment" in text
        and (
            "was made" in text
            or "has been made" in text
            or "already made" in text
        )
    ):
        return "already_paid"

    # Payment delay / settlement.
    #
    # Check delayed-payment requests BEFORE normal payment intent.
    payment_delay_words = (
        "pay tomorrow",
        "payment tomorrow",
        "settle tomorrow",
        "settle it tomorrow",
        "settlement tomorrow",
        "delay the payment",
        "delay payment",
        "delay my payment",
        "defer the payment",
        "defer payment",
        "more time to pay",
        "need more time",
        "give me more time",
        "can i pay later",
        "can i settle later",
        "pay later",
        "settle later",
        "payment later",
        "payment delay",
        "settlement arrangement",
        "payment arrangement"
    )

    if any(phrase in text for phrase in payment_delay_words):
        return "payment_delay"

    # Payment intent.
    payment_words = (
        "make a payment",
        "pay now",
        "want to pay",
        "would like to pay",
        "can i pay",
        "payment plan"
    )

    if any(phrase in text for phrase in payment_words):
        return "payment"

    # Callback / later.
    callback_words = (
        "call me later",
        "call back later",
        "call me back",
        "contact me later",
        "phone me later",
        "try again later"
    )

    if any(phrase in text for phrase in callback_words):
        return "callback"

    # Financial hardship.
    #
    # These statements indicate inability to pay because of
    # financial circumstances rather than a simple refusal.
    hardship_words = (
        "i don't have the money",
        "i do not have the money",
        "don't have money",
        "do not have money",
        "no money",
        "not enough money",
        "can't afford",
        "cannot afford",
        "can't pay",
        "cannot pay",
        "unable to pay",
        "not able to pay",
        "i cannot do this",
        "i can't do this",
        "financial difficulty",
        "financial difficulties",
        "financial problem",
        "financial problems",
        "hardship",
        "lost my job",
        "no income",
        "low income"
    )

    if any(phrase in text for phrase in hardship_words):
        return "financial_hardship"

    # Common STT variations for financial hardship.
    if (
        ("don't" in text or "do not" in text or "cannot" in text or "can't" in text)
        and (
            "money" in text
            or "afford" in text
            or "pay" in text
        )
    ):
        return "financial_hardship"

    # Refusal / unwillingness.
    refusal_words = (
        "don't want to pay",
        "do not want to pay",
        "won't pay",
        "will not pay",
        "can't pay",
        "cannot pay",
        "don't want to"
    )

    if any(phrase in text for phrase in refusal_words):
        return "refusal"

    return "general"


def ask_ollama(transcript, customer=None):

    # Customer context is supplied by the caller phone mapping.
    if customer is None:
        customer = {
            "lead_id": None,
            "phone_number": "",
            "name": "",
            "balance": None,
            "currency": "MYR"
        }

    balance = customer.get("balance")
    name = customer.get("name") or "Customer"

    # Create a natural spoken customer name.
    # Remove demo/test suffixes such as "VER2".
    spoken_name = name.strip()

    name_parts = spoken_name.split()

    if len(name_parts) > 1:
        last_part = name_parts[-1].upper()

        if last_part.startswith("VER"):
            spoken_name = " ".join(name_parts[:-1])

    if not spoken_name:
        spoken_name = "Customer"

    if balance:
        account_context = (
            "Customer name: %s. "
            "Spoken customer name: %s. "
            "Outstanding balance: RM %s. "
            "Currency: Malaysian Ringgit (RM). "
            "Use this balance exactly. "
            "Never invent, estimate, or convert the amount."
            % (name, spoken_name, balance)
        )
    else:
        account_context = (
            "Customer account balance is not available. "
            "Do not provide or invent a balance."
        )

    intent = detect_customer_intent(transcript)

    intent_instructions = {
        "balance": (
            "Customer is asking about the outstanding balance. "
            "State the exact supplied balance clearly as RM %s. "
            "Do not shorten or alter the amount."
            % (balance or "")
        ),
        "already_paid": (
            "Customer says they already paid. "
            "Acknowledge their statement politely. "
            "Do not confirm payment because payment is not verified."
        ),
        "payment": (
            "Customer wants to make a payment. "
            "Acknowledge their intention politely. "
            "Do not claim that payment has been completed."
        ),
        "callback": (
            "Customer wants a callback later. "
            "Acknowledge the request politely. "
            "Do not promise a specific callback time."
        ),
        "refusal": (
            "Customer is unwilling or unable to pay. "
            "Remain polite and non-confrontational. "
            "Do not threaten or pressure the customer."
        ),
        "general": (
            "Respond naturally to the customer's statement."
        ),
        "unknown": (
            "Respond naturally and briefly to the customer's statement."
        )
    }

    intent_context = (
        "Detected customer intent: %s. "
        "%s "
        % (
            intent,
            intent_instructions.get(
                intent,
                intent_instructions["general"]
            )
        )
    )

    system_prompt = (
        "You are HNC Voice AI handling a debt-collection "
        "phone conversation. "
        "Be polite, concise and natural. "
        "Reply in one short spoken response. "
        "Maximum 15 words. "
        "Do not invent customer information, payment amounts, "
        "dates or company details. "
        "Do not claim an action was completed unless confirmed. "
        "Always use the supplied customer account information. "
        "For the first greeting, address the customer using the supplied spoken name exactly once. "
        "After the greeting, do not repeat the customer's name unless necessary. "
        "Do not add Mr, Mrs, Ms, Sir, Madam, or any title. "
        "Keep every response complete and under 15 words. "
        "If an exact balance is supplied, state that exact balance only. "
        "Never say around, approximately, about, roughly, or an estimated amount. "
        "Never change, round, or modify the supplied amount. "
        "Never convert RM into pounds or dollars. "
        + intent_context
        + account_context
    )

    payload = {
        "model": OLLAMA_MODEL,
        "keep_alive": "30m",
        "stream": False,
        "messages": [
            {
                "role": "system",
                "content": system_prompt
            },
            {
                "role": "user",
                "content": (
                    "Customer said:\n"
                    + transcript
                    + "\n\n"
                    "If this is the customer's first greeting, greet them by their supplied spoken name. "
                    "Follow the detected intent instructions exactly. "
                    "Otherwise, respond naturally to what they said. "
                    "Return one complete spoken sentence under 15 words."
                )
            }
        ],
        "options": {
            "temperature": 0.2,
            "num_predict": 16
        }
    }

    data = json.dumps(payload).encode()

    req = urllib.request.Request(
        OLLAMA_URL,
        data=data,
        headers={
            "Content-Type": "application/json"
        }
    )

    start = time.time()

    with urllib.request.urlopen(
        req,
        timeout=60
    ) as response:

        result = json.loads(
            response.read().decode()
        )

    elapsed = time.time() - start

    answer = result.get(
        "message",
        {}
    ).get(
        "content",
        ""
    ).strip()

    return answer, elapsed


def piper_tts(text):

    fd, wav_path = tempfile.mkstemp(
        prefix="hnc_tts_",
        suffix=".wav"
    )

    os.close(fd)

    command = (
        "echo "
        + json.dumps(text)
        + " | piper "
        "-m "
        + json.dumps(PIPER_MODEL)
        + " --length_scale 1.12 -f "
        + json.dumps(wav_path)
    )

    start = time.time()

    rc = os.system(command)

    elapsed = time.time() - start

    if rc != 0:

        try:
            os.unlink(wav_path)
        except OSError:
            pass

        raise RuntimeError(
            "Piper failed with exit code %d"
            % rc
        )

    with wave.open(
        wav_path,
        "rb"
    ) as w:

        channels = w.getnchannels()
        width = w.getsampwidth()
        rate = w.getframerate()
        pcm = w.readframes(
            w.getnframes()
        )

    try:
        os.unlink(wav_path)
    except OSError:
        pass

    if channels != 1:

        pcm = audioop.tomono(
            pcm,
            width,
            1,
            1
        )

        channels = 1

    if rate != RATE_OUT:

        pcm, state = audioop.ratecv(
            pcm,
            width,
            channels,
            rate,
            RATE_OUT,
            None
        )

        rate = RATE_OUT

    return pcm, elapsed


def send_audio(conn, pcm):

    frame_size = 320

    total = 0

    for pos in range(
        0,
        len(pcm),
        frame_size
    ):

        chunk = pcm[
            pos:pos + frame_size
        ]

        frame = struct.pack(
            "!BH",
            0x10,
            len(chunk)
        ) + chunk

        try:
            conn.sendall(frame)
        except (BrokenPipeError, ConnectionResetError) as e:
            print(
                "AUDIO SOCKET CLOSED DURING PLAYBACK:",
                repr(e)
            )
            return total

        total += len(chunk)

        # 20 ms of 8 kHz / 16-bit mono audio.
        time.sleep(
            len(chunk)
            / (
                RATE_OUT
                * WIDTH
                * CHANNELS
            )
        )

    return total


def save_conversation_event(
    call_uuid,
    segment_number,
    phone_number,
    customer,
    customer_text,
    intent,
    ai_response,
    stt_time=0.0,
    ollama_time=0.0,
    piper_time=0.0,
    playback_time=0.0,
    total_processing_time=0.0,
    event_type="conversation"
):
    """
    Save a structured conversation event for reporting.
    """

    report_file = "/var/log/hnc-ai/conversations.jsonl"

    customer = customer or {}

    record = {
        "timestamp": time.strftime(
            "%Y-%m-%d %H:%M:%S"
        ),
        "event_type": event_type,
        "call_uuid": call_uuid or "",
        "segment": segment_number,
        "phone": phone_number or "",
        "lead_id": customer.get("lead_id"),
        "customer_name": customer.get("name") or "",
        "balance": customer.get("balance"),
        "currency": customer.get("currency") or "MYR",
        "customer_text": customer_text or "",
        "intent": intent or "",
        "ai_response": ai_response or "",
        "stt_time": round(
            float(stt_time or 0.0),
            3
        ),
        "ollama_time": round(
            float(ollama_time or 0.0),
            3
        ),
        "piper_time": round(
            float(piper_time or 0.0),
            3
        ),
        "playback_time": round(
            float(playback_time or 0.0),
            3
        ),
        "total_processing_time": round(
            float(total_processing_time or 0.0),
            3
        )
    }

    try:

        with open(
            report_file,
            "a",
            encoding="utf-8"
        ) as f:

            f.write(
                json.dumps(
                    record,
                    ensure_ascii=False
                )
                + "\n"
            )

        print(
            "REPORT SAVED: %s"
            % event_type
        )

    except Exception as e:

        # Reporting must never interrupt the live call.
        print(
            "REPORT SAVE ERROR:",
            repr(e)
        )


def get_time_greeting():
    """
    Return a deterministic time-based AI Demo greeting.
    """

    hour = time.localtime().tm_hour

    if hour < 12:
        return "Good morning"
    elif hour < 18:
        return "Good afternoon"
    else:
        return "Good evening"


def play_tts_response(conn, text, label="TTS"):
    """
    Generate Piper audio and play it through AudioSocket.
    Used for deterministic greeting/goodbye responses.
    """

    print(
        "%s: %s"
        % (label, text)
    )

    tts_start = time.time()

    pcm_out, piper_time = piper_tts(
        text
    )

    print(
        "%s PIPER TIME: %.2fs"
        % (label, piper_time)
    )

    playback_start = time.time()

    bytes_sent = send_audio(
        conn,
        pcm_out
    )

    playback_time = (
        time.time()
        - playback_start
    )

    print(
        "%s PLAYBACK: %.2fs | %d bytes"
        % (
            label,
            playback_time,
            bytes_sent
        )
    )

    return bytes_sent


def process_segment(
    pcm8,
    segment_number,
    conn,
    call_uuid=None
):

    duration = len(pcm8) / (
        RATE_IN * WIDTH
    )

    # Convert incoming 8 kHz telephone audio
    # to 16 kHz for Whisper.
    pcm16, state = audioop.ratecv(
        pcm8,
        WIDTH,
        CHANNELS,
        RATE_IN,
        16000,
        None
    )

    fd, wav_path = tempfile.mkstemp(
        prefix="hnc_stt_",
        suffix=".wav"
    )

    os.close(fd)

    try:

        with wave.open(
            wav_path,
            "wb"
        ) as w:

            w.setnchannels(1)
            w.setsampwidth(WIDTH)
            w.setframerate(16000)
            w.writeframes(pcm16)

        stt_start = time.time()

        segments, info = whisper_model.transcribe(
            wav_path,
            language="en",
            beam_size=5,
            vad_filter=True,
            vad_parameters=dict(
                min_silence_duration_ms=500
            ),
            condition_on_previous_text=False,
            initial_prompt=(
                "This is a real telephone conversation. "
                "Transcribe exactly what the caller says. "
                "Do not paraphrase or guess. "
                "Common Indian names and English names may be spoken. "
                "The caller may say a name such as "
                "O.P. Chauhan, Openra Chauhan, or O P Chauhan. "
                "Payment, account, amount, date and phone-number "
                "words may occur."
            )
        )

        texts = []

        for segment in segments:

            text = segment.text.strip()

            if text:
                texts.append(text)

        transcript = " ".join(texts)

        stt_time = time.time() - stt_start

        print()
        print(
            "SEGMENT #%d | %.2fs audio | STT %.2fs"
            % (
                segment_number,
                duration,
                stt_time
            )
        )

        if not transcript:

            print(
                "TRANSCRIPT: [no speech detected]"
            )

            return

        # Ignore extremely short / unreliable STT results.
        # This prevents noise fragments such as "I", "Oh",
        # or similar one-word hallucinations from triggering AI.
        if len(transcript.strip()) < 3:

            print(
                "TRANSCRIPT: [too short - ignored]"
            )

            return

        print(
            "TRANSCRIPT:",
            transcript
        )

        # -------------------------
        # Customer context
        # -------------------------
        #
        # Resolve customer before intent/hangup handling so
        # every report event can include account information.
        phone_number = get_phone_from_uuid(
            call_uuid
        )

        customer = get_customer_context(
            phone_number
        )

        # -------------------------
        # Customer hangup detection
        # -------------------------
        #
        # Detect explicit requests to end the call.
        # Do this before Ollama/TTS so no normal AI response
        # is generated.
        normalized = " ".join(
            transcript.lower().strip().split()
        )

        hangup_phrases = (
            "hang up",
            "hangup",
            "disconnect",
            "disconnect the call",
            "end the call",
            "end this call",
            "terminate the call",
            "terminate this call",
            "goodbye",
            "good bye",
            "bye bye",
            "bye"
        )

        should_hangup = any(
            phrase in normalized
            for phrase in hangup_phrases
        )

        if should_hangup:

            print(
                "CALL CONTROL: Customer requested hangup"
            )

            try:

                goodbye_text = (
                    "Thank you for calling AI Demo. Goodbye."
                )

                goodbye_start = time.time()

                play_tts_response(
                    conn,
                    goodbye_text,
                    "GOODBYE"
                )

                goodbye_elapsed = (
                    time.time()
                    - goodbye_start
                )

                save_conversation_event(
                    call_uuid,
                    segment_number,
                    phone_number,
                    customer,
                    transcript,
                    "hangup",
                    goodbye_text,
                    stt_time,
                    0.0,
                    0.0,
                    goodbye_elapsed,
                    stt_time + goodbye_elapsed,
                    "goodbye"
                )

            except (
                BrokenPipeError,
                ConnectionResetError
            ) as e:

                print(
                    "GOODBYE AUDIO SOCKET CLOSED:",
                    repr(e)
                )

            except Exception as e:

                print(
                    "GOODBYE TTS ERROR:",
                    repr(e)
                )

            return "HANGUP"

        # -------------------------
        # Ollama
        # -------------------------

        ai_start = time.time()

        try:

            intent = detect_customer_intent(
                transcript
            )

            deterministic_responses = {
                "balance": (
                    "Your outstanding balance is RM %s."
                    % (
                        customer.get("balance")
                        or ""
                    )
                ),
                "already_paid": (
                    "I understand. Your payment needs to be verified before we update your account."
                ),
                "payment": (
                    "Certainly. I can help you with the payment process."
                ),
                "payment_delay": (
                    "I understand. We can discuss a suitable payment date or arrangement."
                ),
                "financial_hardship": (
                    "I understand. We can discuss your account and possible payment arrangements."
                ),
                "callback": (
                    "Certainly. I understand you'd like us to contact you later."
                ),
                "refusal": (
                    "I understand. We can discuss your account when you're ready."
                )
            }

            if intent in deterministic_responses:

                answer = deterministic_responses[
                    intent
                ]

                ollama_time = 0.0

                print(
                    "INTENT:",
                    intent
                )

                print(
                    "DETERMINISTIC RESPONSE: True"
                )

            else:

                answer, ollama_time = ask_ollama(
                    transcript,
                    customer
                )

        except Exception as e:

            print(
                "OLLAMA ERROR:",
                repr(e)
            )

            return

        print(
            "AI RESPONSE:",
            answer
        )

        print(
            "OLLAMA TIME: %.2fs"
            % ollama_time
        )

        # -------------------------
        # Piper
        # -------------------------

        try:

            tts_start = time.time()

            pcm_out, piper_time = piper_tts(
                answer
            )

            print(
                "PIPER TIME: %.2fs"
                % piper_time
            )

            print(
                "TTS AUDIO: %.2fs"
                % (
                    len(pcm_out)
                    / (
                        RATE_OUT
                        * WIDTH
                    )
                )
            )

        except Exception as e:

            print(
                "PIPER ERROR:",
                repr(e)
            )

            return

        # -------------------------
        # AudioSocket playback
        # -------------------------

        try:

            playback_start = time.time()

            bytes_sent = send_audio(
                conn,
                pcm_out
            )

            playback_time = (
                time.time()
                - playback_start
            )

            print(
                "PLAYBACK: %.2fs | %d bytes"
                % (
                    playback_time,
                    bytes_sent
                )
            )

            # -------------------------------------------------
            # Clear stale incoming audio captured during AI
            # processing/playback before listening again.
            # This prevents AI playback from becoming the next
            # STT segment.
            # -------------------------------------------------
            try:

                conn.setblocking(False)

                drained_bytes = 0

                while True:

                    try:
                        stale = conn.recv(4096)

                    except BlockingIOError:
                        break

                    if not stale:
                        break

                    drained_bytes += len(stale)

                print(
                    "AUDIO DRAIN: %d bytes"
                    % drained_bytes
                )

            except Exception as e:

                print(
                    "AUDIO DRAIN ERROR:",
                    repr(e)
                )

            finally:

                try:
                    conn.setblocking(True)
                except Exception:
                    pass

        except Exception as e:

            print(
                "PLAYBACK ERROR:",
                repr(e)
            )

            raise

        total_processing = (
            stt_time
            + ollama_time
            + piper_time
        )

        print(
            "PROCESSING TIME: %.2fs"
            % total_processing
        )

        # -------------------------------------------------
        # Conversation report
        # Save only after Piper and playback have completed,
        # so all performance metrics contain real values.
        # -------------------------------------------------
        save_conversation_event(
            call_uuid,
            segment_number,
            phone_number,
            customer,
            transcript,
            intent,
            answer,
            stt_time,
            ollama_time,
            piper_time,
            playback_time,
            total_processing,
            "conversation"
        )

    finally:

        try:
            os.unlink(wav_path)
        except OSError:
            pass


server = socket.socket(
    socket.AF_INET,
    socket.SOCK_STREAM
)

server.setsockopt(
    socket.SOL_SOCKET,
    socket.SO_REUSEADDR,
    1
)

server.bind(
    (HOST, PORT)
)

server.listen(5)

print()
print("Waiting for AudioSocket connection...")


try:

    while True:

        conn, addr = server.accept()

        print()
        print("CONNECTED:", addr)

        speech_buffer = bytearray()
        silence_frames = 0
        speech_frames = 0
        in_speech = False
        segment_number = 0
        call_uuid = None
        call_uuid = None

        try:

            while True:

                header = conn.recv(3)

                if not header:
                    break

                if len(header) != 3:
                    break

                frame_type = header[0]

                frame_length = struct.unpack(
                    "!H",
                    header[1:3]
                )[0]

                data = b""

                while len(data) < frame_length:

                    part = conn.recv(
                        frame_length - len(data)
                    )

                    if not part:
                        break

                    data += part

                if len(data) != frame_length:
                    break

                # AudioSocket UUID frame: type 0x01, 16-byte UUID.
                if frame_type == 0x01 and len(data) == 16:
                    call_uuid = "%08x-%04x-%04x-%04x-%012x" % (
                        int.from_bytes(data[0:4], "big"),
                        int.from_bytes(data[4:6], "big"),
                        int.from_bytes(data[6:8], "big"),
                        int.from_bytes(data[8:10], "big"),
                        int.from_bytes(data[10:16], "big")
                    )

                    print(
                        "AUDIO_SOCKET UUID:",
                        call_uuid
                    )

                    # Resolve customer before the first greeting.
                    greeting_phone = get_phone_from_uuid(
                        call_uuid
                    )

                    greeting_customer = get_customer_context(
                        greeting_phone
                    )

                    greeting_name = (
                        greeting_customer.get("name")
                        or "Customer"
                    )

                    # Remove demo/test suffixes such as VER2.
                    greeting_spoken_name = (
                        greeting_name.strip()
                    )

                    greeting_parts = (
                        greeting_spoken_name.split()
                    )

                    if len(greeting_parts) > 1:
                        greeting_last = (
                            greeting_parts[-1].upper()
                        )

                        if greeting_last.startswith("VER"):
                            greeting_spoken_name = (
                                " ".join(
                                    greeting_parts[:-1]
                                )
                            )

                    if not greeting_spoken_name:
                        greeting_spoken_name = "Customer"

                    greeting = (
                        get_time_greeting()
                        + ", my name is AI Demo. "
                        + "How may I assist you today?"
                    )

                    print(
                        "AUTOMATIC GREETING:",
                        greeting
                    )

                    try:

                        play_tts_response(
                            conn,
                            greeting,
                            "GREETING"
                        )

                        # -------------------------------------------------
                        # Drain audio captured while the automatic greeting
                        # was playing. This prevents Whisper from treating
                        # the AI greeting as customer speech.
                        # -------------------------------------------------
                        try:

                            conn.setblocking(False)

                            greeting_drained = 0

                            while True:

                                try:
                                    stale = conn.recv(4096)

                                except BlockingIOError:
                                    break

                                if not stale:
                                    break

                                greeting_drained += len(stale)

                            print(
                                "GREETING AUDIO DRAIN: %d bytes"
                                % greeting_drained
                            )

                        except Exception as e:

                            print(
                                "GREETING AUDIO DRAIN ERROR:",
                                repr(e)
                            )

                        finally:

                            try:
                                conn.setblocking(True)
                            except Exception:
                                pass

                    except (
                        BrokenPipeError,
                        ConnectionResetError
                    ) as e:

                        print(
                            "GREETING AUDIO SOCKET CLOSED:",
                            repr(e)
                        )

                    except Exception as e:

                        print(
                            "GREETING TTS ERROR:",
                            repr(e)
                        )

                    continue

                # Ignore non-audio frames.
                if frame_type != 0x10:
                    continue

                level = rms_level(data)

                is_speech = (
                    level >=
                    SPEECH_RMS_THRESHOLD
                )

                if is_speech:

                    speech_frames += 1

                    if not in_speech and speech_frames >= SPEECH_START_FRAMES:

                        print()
                        print(
                            "SPEECH START | RMS:",
                            round(level, 1)
                        )

                        in_speech = True

                    speech_buffer.extend(data)
                    silence_frames = 0

                else:

                    speech_frames = 0

                    if in_speech:

                        speech_buffer.extend(data)

                        silence_frames += 1

                        if (
                            len(speech_buffer)
                            >= MIN_SPEECH_BYTES
                            and
                            silence_frames
                            >= END_SILENCE_FRAMES
                        ):

                            segment_number += 1

                            result = process_segment(
                                bytes(
                                    speech_buffer
                                ),
                                segment_number,
                                conn,
                                call_uuid
                            )

                            speech_buffer.clear()
                            silence_frames = 0
                            in_speech = False

                            if result == "HANGUP":
                                print(
                                    "CALL CONTROL: Clean hangup"
                                )
                                break

                if (
                    in_speech
                    and
                    len(speech_buffer)
                    >= MAX_SPEECH_BYTES
                ):

                    segment_number += 1

                    process_segment(
                        bytes(
                            speech_buffer
                        ),
                        segment_number,
                        conn,
                        call_uuid
                    )

                    speech_buffer.clear()
                    silence_frames = 0
                    in_speech = False

        except Exception as e:

            print(
                "CONNECTION ERROR:",
                repr(e)
            )

        finally:

            conn.close()

            print(
                "DISCONNECTED:",
                addr
            )

except KeyboardInterrupt:

    print()
    print("Stopping HNC Full Voice AI...")

finally:

    server.close()
