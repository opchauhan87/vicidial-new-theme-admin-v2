#!/usr/bin/env python3

import socket
import struct
import wave
import time
import os
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

SPEECH_RMS_THRESHOLD = 320
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


def ask_ollama(transcript):

    system_prompt = (
        "You are HNC Voice AI handling a debt-collection "
        "phone conversation. "
        "Be polite, concise and natural. "
        "Reply in one short spoken response. "
        "Maximum 15 words. "
        "Do not invent customer information, payment amounts, "
        "dates or company details. "
        "Do not claim an action was completed unless confirmed."
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
                    "Give the next short spoken response."
                )
            }
        ],
        "options": {
            "temperature": 0.2,
            "num_predict": 20
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
        + " -f "
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

        conn.sendall(frame)

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


def process_segment(
    pcm8,
    segment_number,
    conn
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
                "This is an HNC call-center conversation. "
                "Customer names, Indian names, dates, "
                "payment amounts and phone numbers may be spoken."
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

        print(
            "TRANSCRIPT:",
            transcript
        )

        # -------------------------
        # Ollama
        # -------------------------

        ai_start = time.time()

        try:

            answer, ollama_time = ask_ollama(
                transcript
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

                            process_segment(
                                bytes(
                                    speech_buffer
                                ),
                                segment_number,
                                conn
                            )

                            speech_buffer.clear()
                            silence_frames = 0
                            in_speech = False

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
                        conn
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
