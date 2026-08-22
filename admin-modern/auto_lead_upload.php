<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ViciDial Lead Upload Tool - Direct SQL Generator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .upload-section {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #fafafa;
        }

        .upload-section:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .upload-section.drag-over {
            border-color: #667eea;
            background: #f0f2ff;
        }

        .file-input {
            display: none;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }

        .preview-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }

        .preview-section.active {
            display: block;
            animation: slideDown 0.3s ease;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .mapping-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 13px;
        }

        .mapping-table th, .mapping-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .mapping-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            position: sticky;
            top: 0;
        }

        .mapping-table tbody tr:hover {
            background: #f5f5f5;
        }

        .status-message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }

        .status-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 10px;
            display: none;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: 600;
        }

        .campaign-list {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid #e0e0e0;
        }

        .campaign-tag {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            margin: 5px;
            font-size: 12px;
        }

        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid #e0e0e0;
        }

        .sql-preview {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin-top: 10px;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .copy-btn {
            background: #28a745;
            margin-top: 10px;
            margin-right: 10px;
        }

        .auto-select-badge {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 8px;
        }

        .record-count {
            font-size: 24px;
            font-weight: bold;
            color: #2196f3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>?? ViciDial Lead Upload Tool</h1>
            <p>Generate SQL INSERT Statements for vicidial_list | Auto Numeric List ID | Auto Campaign Detection</p>
        </div>

        <div class="content">
            <div class="upload-section" id="uploadSection">
                <div class="upload-icon">??</div>
                <h3>Click or Drag & Drop Excel/CSV File</h3>
                <p style="color: #666; margin-top: 10px;">Supported formats: .xlsx, .xls, .csv</p>
                <input type="file" id="fileInput" class="file-input" accept=".xlsx,.xls,.csv">
                <button class="btn" id="selectFileBtn" style="margin-top: 20px;">Select File</button>
            </div>

            <div id="previewSection" class="preview-section">
                <div class="info-box">
                    <strong>? Auto-Generated Numeric List ID:</strong> <span id="displayListId" style="font-weight: bold; font-size: 18px; color: #2196f3;"></span>
                    <br>
                    <strong>?? Auto-Detected Campaign Column:</strong> <span id="detectedCampaign" style="font-weight: bold; color: #28a745;"></span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>?? List ID (Auto-generated):</label>
                        <input type="number" id="listId" readonly style="background: #e8f0fe; font-family: monospace; font-weight: bold; font-size: 16px;">
                    </div>

                    <div class="form-group">
                        <label>??? List Name:</label>
                        <input type="text" id="listName" placeholder="Enter list name">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>?? Campaign Column (Auto-detected from Excel):</label>
                        <select id="campaignSelect">
                            <option value="">-- No Campaign Column --</option>
                        </select>
                        <small>Select which Excel column contains campaign names</small>
                    </div>

                    <div class="form-group">
                        <label>?? Default Status:</label>
                        <select id="defaultStatus">
                            <option value="NEW">NEW - New Lead</option>
                            <option value="ACTIVE">ACTIVE - Active Lead</option>
                            <option value="QUEUE">QUEUE - In Queue</option>
                            <option value="CALLBACK">CALLBACK - Callback</option>
                            <option value="DNC">DNC - Do Not Call</option>
                        </select>
                    </div>
                </div>

                <div class="campaign-list" id="campaignList" style="display: none;">
                    <strong>?? Detected Campaigns from Excel:</strong>
                    <div id="campaignTags"></div>
                </div>

                <div class="warning-box" id="warningBox" style="display: none;">
                    <strong>?? Warning:</strong> <span id="warningMessage"></span>
                </div>

                <h3>?? Field Mapping to vicidial_list</h3>
                <div style="overflow-x: auto; max-height: 500px;">
                    <table class="mapping-table" id="mappingTable">
                        <thead>
                            <tr>
                                <th style="width: 25%">Excel Column</th>
                                <th style="width: 35%">vicidial_list Field</th>
                                <th style="width: 20%">Sample Data</th>
                                <th style="width: 20%">Status</th>
                            </tr>
                        </thead>
                        <tbody id="mappingBody">
                        </tbody>
                    </table>
                </div>

                <div class="stats-card" id="statsCard" style="display: none;">
                    <h4>?? Upload Statistics</h4>
                    <div id="statsContent"></div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button class="btn btn-success" id="generateSqlBtn">?? Generate SQL Statements</button>
                    <button class="btn btn-secondary" id="resetBtn">?? Reset</button>
                </div>

                <div class="progress-bar" id="progressBar">
                    <div class="progress-fill" id="progressFill">0%</div>
                </div>

                <div id="statusMessage" class="status-message"></div>
                
                <div id="sqlOutput" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                        <h3>?? Generated SQL Statements</h3>
                        <div>
                            <button class="btn copy-btn" id="copySqlBtn">?? Copy to Clipboard</button>
                            <button class="btn btn-secondary" id="downloadSqlBtn">?? Download SQL File</button>
                        </div>
                    </div>
                    <div id="sqlPreview" class="sql-preview"></div>
                    <div class="info-box" style="margin-top: 10px;">
                        <strong>?? How to use:</strong><br>
                        1. Copy the SQL statements above<br>
                        2. Connect to your MariaDB/MySQL database<br>
                        3. Run: <code>USE asterisk;</code><br>
                        4. Paste and execute the SQL statements<br>
                        5. Verify with: <code>SELECT COUNT(*) FROM vicidial_list WHERE list_id = <span id="verifyListId"></span>;</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        let currentData = null;
        let currentHeaders = [];
        let generatedListId = null;
        let existingListIds = new Set();
        let generatedSQL = [];

        // Load existing list IDs from localStorage
        function loadExistingListIds() {
            const existing = localStorage.getItem('vicidial_used_list_ids');
            if (existing) {
                existingListIds = new Set(JSON.parse(existing));
            }
        }

        // Generate numeric list ID (10000-99999)
        function generateNumericListId() {
            let listId;
            do {
                listId = Math.floor(Math.random() * 90000) + 10000;
            } while (existingListIds.has(listId));
            return listId;
        }

        // Show status message
        function showStatus(message, type = 'info') {
            const statusMessage = document.getElementById('statusMessage');
            statusMessage.textContent = message;
            statusMessage.className = `status-message ${type}`;
            statusMessage.style.display = 'block';
            
            setTimeout(() => {
                if (statusMessage.style.display !== 'none') {
                    statusMessage.style.display = 'none';
                }
            }, 5000);
        }

        // Update progress
        function updateProgress(percent, text) {
            const progressBar = document.getElementById('progressBar');
            const progressFill = document.getElementById('progressFill');
            progressBar.style.display = 'block';
            progressFill.style.width = `${percent}%`;
            progressFill.textContent = text || `${percent}%`;
        }

        // Auto-detect campaign column from headers
        function detectCampaignColumn(headers) {
            const campaignKeywords = ['campaign', 'campaign_id', 'campaignid', 'camp', 'list_name', 'campaign_name', 'campname', 'campaignname'];
            
            for (let header of headers) {
                const lowerHeader = header.toLowerCase();
                if (campaignKeywords.some(keyword => lowerHeader === keyword || lowerHeader.includes(keyword))) {
                    return header;
                }
            }
            return null;
        }

        // Get unique campaigns from data (filtering empty values)
        function getUniqueCampaigns(data, campaignColumn) {
            if (!campaignColumn) return [];
            const campaigns = new Set();
            data.forEach(row => {
                const value = row[campaignColumn];
                if (value && value.toString().trim() && value.toString().trim() !== '') {
                    campaigns.add(value.toString().trim());
                }
            });
            return Array.from(campaigns);
        }

        // Parse Excel/CSV file - FIXED: Properly filter empty rows
        function parseFile(file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                try {
                    let rawData = [];
                    
                    if (file.name.endsWith('.csv')) {
                        const text = e.target.result;
                        const lines = text.split('\n').filter(line => line.trim());
                        if (lines.length < 2) {
                            showStatus('File has no data rows', 'error');
                            return;
                        }
                        
                        const headers = lines[0].split(',').map(h => h.trim().replace(/^"|"$/g, ''));
                        
                        for (let i = 1; i < lines.length; i++) {
                            if (lines[i].trim()) {
                                const values = lines[i].split(',').map(v => v.trim().replace(/^"|"$/g, ''));
                                if (values.length >= headers.length) {
                                    const row = {};
                                    headers.forEach((header, index) => {
                                        row[header] = values[index] || '';
                                    });
                                    // Only add if at least one field has a value
                                    if (Object.values(row).some(v => v && v.trim())) {
                                        rawData.push(row);
                                    }
                                }
                            }
                        }
                    } else {
                        const workbook = XLSX.read(e.target.result, { type: 'array' });
                        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                        const jsonData = XLSX.utils.sheet_to_json(firstSheet, { defval: '' });
                        
                        // Filter out completely empty rows
                        rawData = jsonData.filter(row => {
                            return Object.values(row).some(value => value && value.toString().trim());
                        });
                    }
                    
                    if (rawData.length === 0) {
                        showStatus('No valid data found in file. Please check your file format.', 'error');
                        return;
                    }
                    
                    currentData = rawData;
                    currentHeaders = Object.keys(rawData[0]);
                    
                    // Generate numeric list ID
                    generatedListId = generateNumericListId();
                    document.getElementById('listId').value = generatedListId;
                    document.getElementById('displayListId').textContent = generatedListId;
                    document.getElementById('verifyListId').textContent = generatedListId;
                    document.getElementById('listName').value = `List_${generatedListId}_${Date.now()}`;
                    
                    // Auto-detect campaign column
                    const detectedCampaign = detectCampaignColumn(currentHeaders);
                    
                    // Populate campaign select dropdown
                    const campaignSelect = document.getElementById('campaignSelect');
                    campaignSelect.innerHTML = '<option value="">-- No Campaign Column --</option>';
                    currentHeaders.forEach(header => {
                        const option = document.createElement('option');
                        option.value = header;
                        option.textContent = header;
                        if (detectedCampaign === header) {
                            option.selected = true;
                            option.style.background = '#e8f0fe';
                            option.style.fontWeight = 'bold';
                        }
                        campaignSelect.appendChild(option);
                    });
                    
                    if (detectedCampaign) {
                        document.getElementById('detectedCampaign').innerHTML = `${detectedCampaign} <span class="auto-select-badge">Auto-detected</span>`;
                        const campaigns = getUniqueCampaigns(currentData, detectedCampaign);
                        if (campaigns.length > 0) {
                            displayCampaigns(campaigns, currentData, detectedCampaign);
                        }
                    } else {
                        document.getElementById('detectedCampaign').innerHTML = 'No campaign column detected';
                    }
                    
                    // Check for potential row count issues
                    if (rawData.length > 10000) {
                        document.getElementById('warningMessage').innerHTML = `Large file detected: ${rawData.length.toLocaleString()} rows. This may generate a large SQL file. Consider splitting into smaller batches.`;
                        document.getElementById('warningBox').style.display = 'block';
                    } else {
                        document.getElementById('warningBox').style.display = 'none';
                    }
                    
                    // Build mapping table
                    buildMappingTable(currentHeaders);
                    
                    // Show preview section
                    document.getElementById('previewSection').classList.add('active');
                    document.getElementById('sqlOutput').style.display = 'none';
                    
                    showStatus(`? Loaded ${rawData.length.toLocaleString()} valid records successfully! List ID: ${generatedListId}`, 'success');
                    
                } catch (error) {
                    console.error('Parse error:', error);
                    showStatus('Error parsing file: ' + error.message, 'error');
                }
            };
            
            reader.onerror = function() {
                showStatus('Error reading file', 'error');
            };
            
            if (file.name.endsWith('.csv')) {
                reader.readAsText(file, 'UTF-8');
            } else {
                reader.readAsArrayBuffer(file);
            }
        }

        // Display detected campaigns with counts
        function displayCampaigns(campaigns, data, campaignColumn) {
            const campaignListDiv = document.getElementById('campaignList');
            const campaignTagsDiv = document.getElementById('campaignTags');
            
            if (campaigns.length > 0) {
                const campaignCounts = {};
                campaigns.forEach(c => { campaignCounts[c] = 0; });
                data.forEach(row => {
                    const campaign = row[campaignColumn];
                    if (campaign && campaignCounts[campaign] !== undefined) {
                        campaignCounts[campaign]++;
                    }
                });
                
                campaignListDiv.style.display = 'block';
                campaignTagsDiv.innerHTML = campaigns.map(c => 
                    `<span class="campaign-tag">?? ${c}: ${campaignCounts[c]} leads</span>`
                ).join('');
            } else {
                campaignListDiv.style.display = 'none';
            }
        }

        // Update campaigns when campaign column changes
        function updateCampaigns() {
            const campaignColumn = document.getElementById('campaignSelect').value;
            if (campaignColumn && currentData) {
                const campaigns = getUniqueCampaigns(currentData, campaignColumn);
                displayCampaigns(campaigns, currentData, campaignColumn);
            } else {
                document.getElementById('campaignList').style.display = 'none';
            }
        }

        // Build field mapping table
        function buildMappingTable(headers) {
            const mappingBody = document.getElementById('mappingBody');
            mappingBody.innerHTML = '';
            
            const importantFields = ['phone_number', 'first_name', 'last_name', 'email', 'address1', 'city', 'state', 'postal_code'];
            
            headers.forEach(header => {
                const row = mappingBody.insertRow();
                
                const cell1 = row.insertCell(0);
                cell1.textContent = header;
                cell1.style.fontWeight = 'bold';
                
                const cell2 = row.insertCell(1);
                const select = document.createElement('select');
                select.className = 'vicidial-field-select';
                select.setAttribute('data-excel-field', header);
                select.style.width = '100%';
                
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = '-- Skip Field --';
                select.appendChild(defaultOption);
                
                // Common fields
                const commonFields = [
                    'phone_number', 'first_name', 'last_name', 'email', 'address1', 
                    'city', 'state', 'postal_code', 'vendor_lead_code', 'source_id',
                    'alt_phone', 'comments', 'title', 'cust_id', 'orde_no', 'product'
                ];
                
                commonFields.forEach(field => {
                    const option = document.createElement('option');
                    option.value = field;
                    option.textContent = field.replace(/_/g, ' ').toUpperCase();
                    
                    const lowerHeader = header.toLowerCase();
                    if ((field === 'phone_number' && lowerHeader.includes('phone')) ||
                        (field === 'first_name' && (lowerHeader.includes('first') || lowerHeader.includes('fname'))) ||
                        (field === 'last_name' && (lowerHeader.includes('last') || lowerHeader.includes('lname'))) ||
                        (field === 'email' && lowerHeader.includes('email')) ||
                        (field === 'address1' && lowerHeader.includes('address')) ||
                        (field === 'city' && lowerHeader === 'city') ||
                        (field === 'state' && lowerHeader === 'state') ||
                        (field === 'postal_code' && (lowerHeader.includes('zip') || lowerHeader.includes('postal')))) {
                        option.selected = true;
                    }
                    
                    select.appendChild(option);
                });
                
                cell2.appendChild(select);
                
                const cell3 = row.insertCell(2);
                const sampleValue = currentData[0][header];
                cell3.textContent = sampleValue ? (sampleValue.length > 30 ? sampleValue.substring(0, 30) + '...' : sampleValue) : '-';
                cell3.style.color = '#666';
                
                const cell4 = row.insertCell(3);
                if (header.toLowerCase().includes('phone')) {
                    cell4.innerHTML = '<span style="color: #dc3545;">?? Required</span>';
                } else {
                    cell4.innerHTML = '<span style="color: #28a745;">Optional</span>';
                }
            });
        }

        // Generate SQL statements
        function generateSQL() {
            if (!currentData || currentData.length === 0) {
                showStatus('No data to process', 'error');
                return;
            }
            
            const mapping = {};
            const selectElements = document.querySelectorAll('.vicidial-field-select');
            selectElements.forEach(select => {
                const excelField = select.getAttribute('data-excel-field');
                const vicidialField = select.value;
                if (vicidialField) {
                    mapping[excelField] = vicidialField;
                }
            });
            
            const hasPhoneMapping = Object.values(mapping).includes('phone_number');
            if (!hasPhoneMapping) {
                showStatus('? Phone number field is required. Please map a column to phone_number.', 'error');
                return;
            }
            
            const campaignColumn = document.getElementById('campaignSelect').value;
            const listId = parseInt(document.getElementById('listId').value);
            const listName = document.getElementById('listName').value;
            const defaultStatus = document.getElementById('defaultStatus').value;
            const currentDateTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
            
            if (!listName) {
                showStatus('Please enter a list name', 'error');
                return;
            }
            
            updateProgress(0, 'Generating SQL statements...');
            
            const sqlStatements = [];
            const campaignCounts = {};
            let validRecords = 0;
            let skippedRecords = 0;
            
            for (let i = 0; i < currentData.length; i++) {
                const row = currentData[i];
                
                // Check if phone number exists
                let phoneNumber = null;
                for (const [excelField, vicidialField] of Object.entries(mapping)) {
                    if (vicidialField === 'phone_number') {
                        phoneNumber = row[excelField];
                        break;
                    }
                }
                
                if (!phoneNumber || !phoneNumber.toString().trim()) {
                    skippedRecords++;
                    continue;
                }
                
                const fields = ['entry_date', 'modify_date', 'status', 'list_id', 'called_since_last_reset', 'phone_code', 'called_count', 'rank', 'entry_list_id'];
                const values = [
                    `'${currentDateTime}'`,
                    `'${currentDateTime}'`,
                    `'${defaultStatus}'`,
                    listId,
                    "'N'",
                    "'1'",
                    '0',
                    '0',
                    '0'
                ];
                
                // Add campaign if column selected
                if (campaignColumn && row[campaignColumn] && row[campaignColumn].toString().trim()) {
                    const campaign = row[campaignColumn].toString().trim();
                    fields.push('campaign_id');
                    values.push(`'${campaign.replace(/'/g, "''")}'`);
                    campaignCounts[campaign] = (campaignCounts[campaign] || 0) + 1;
                }
                
                // Add mapped fields
                for (const [excelField, vicidialField] of Object.entries(mapping)) {
                    let value = row[excelField];
                    if (value && value.toString().trim()) {
                        if (vicidialField === 'phone_number') {
                            value = value.toString().replace(/\D/g, '');
                            if (value.length === 11 && value.startsWith('1')) {
                                value = value.substring(1);
                            }
                            if (value.length === 10) {
                                fields.push(vicidialField);
                                values.push(`'${value}'`);
                            }
                        } else if (!fields.includes(vicidialField)) {
                            fields.push(vicidialField);
                            values.push(`'${value.toString().replace(/'/g, "''")}'`);
                        }
                    }
                }
                
                const sql = `INSERT INTO vicidial_list (${fields.join(', ')}) VALUES (${values.join(', ')});`;
                sqlStatements.push(sql);
                validRecords++;
                
                if ((i + 1) % 1000 === 0) {
                    updateProgress(Math.round(((i + 1) / currentData.length) * 100), `Generating ${i + 1}/${currentData.length}`);
                }
            }
            
            updateProgress(100, 'Complete!');
            
            generatedSQL = sqlStatements;
            
            // Display statistics
            displayStats(validRecords, skippedRecords, campaignCounts, listId, listName);
            
            // Display SQL preview
            displaySQLPreview(sqlStatements, validRecords, listId);
            
            showStatus(`? SQL generated successfully! ${validRecords.toLocaleString()} valid records, ${skippedRecords} skipped (no phone number)`, 'success');
            
            setTimeout(() => {
                document.getElementById('progressBar').style.display = 'none';
            }, 2000);
        }
        
        function displayStats(validRecords, skippedRecords, campaignCounts, listId, listName) {
            const statsCard = document.getElementById('statsCard');
            const statsContent = document.getElementById('statsContent');
            
            statsCard.style.display = 'block';
            statsContent.innerHTML = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="stat-item" style="padding: 10px; background: #f8f9fa; border-radius: 5px;">
                        <div class="stat-label">List ID</div>
                        <div class="stat-value" style="font-size: 18px; font-weight: bold;">${listId}</div>
                    </div>
                    <div class="stat-item" style="padding: 10px; background: #f8f9fa; border-radius: 5px;">
                        <div class="stat-label">List Name</div>
                        <div class="stat-value" style="font-size: 18px; font-weight: bold;">${listName}</div>
                    </div>
                    <div class="stat-item" style="padding: 10px; background: #f8f9fa; border-radius: 5px;">
                        <div class="stat-label">Valid Records</div>
                        <div class="stat-value" style="font-size: 18px; font-weight: bold; color: #28a745;">${validRecords.toLocaleString()}</div>
                    </div>
                    <div class="stat-item" style="padding: 10px; background: #f8f9fa; border-radius: 5px;">
                        <div class="stat-label">Skipped Records</div>
                        <div class="stat-value" style="font-size: 18px; font-weight: bold; color: #dc3545;">${skippedRecords.toLocaleString()}</div>
                    </div>
                </div>
            `;
            
            if (Object.keys(campaignCounts).length > 0) {
                statsContent.innerHTML += `
                    <h4 style="margin-top: 15px;">Campaign Breakdown:</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        ${Object.entries(campaignCounts).map(([camp, count]) => `
                            <div class="stat-item" style="padding: 10px; background: #f8f9fa; border-radius: 5px;">
                                <div class="stat-label">Campaign: ${camp}</div>
                                <div class="stat-value" style="font-size: 16px;">${count.toLocaleString()} leads</div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
        }
        
        function displaySQLPreview(sqlStatements, totalRecords, listId) {
            const sqlPreview = document.getElementById('sqlPreview');
            const sqlOutput = document.getElementById('sqlOutput');
            
            // Show first 10 statements as preview
            const previewLimit = Math.min(10, sqlStatements.length);
            const previewStatements = sqlStatements.slice(0, previewLimit).join('\n');
            const moreInfo = sqlStatements.length > previewLimit ? `\n\n... and ${sqlStatements.length - previewLimit} more statements` : '';
            
            sqlPreview.innerHTML = `-- ViciDial Lead Upload\n-- List ID: ${listId}\n-- Total Records: ${totalRecords.toLocaleString()}\n-- Generated: ${new Date().toLocaleString()}\n-- \n-- To execute: USE asterisk;\n-- \n\n${previewStatements}${moreInfo}`;
            sqlOutput.style.display = 'block';
            
            // Scroll to SQL output
            sqlOutput.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        function copyToClipboard() {
            const sqlPreview = document.getElementById('sqlPreview');
            const text = sqlPreview.innerText;
            
            navigator.clipboard.writeText(text).then(() => {
                showStatus('? SQL copied to clipboard!', 'success');
            }).catch(() => {
                showStatus('Failed to copy. Please select manually.', 'error');
            });
        }
        
        function downloadSQL() {
            const sqlPreview = document.getElementById('sqlPreview');
            const text = sqlPreview.innerText;
            const listId = document.getElementById('listId').value;
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `vicidial_leads_list_${listId}.sql`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showStatus('? SQL file downloaded!', 'success');
        }
        
        function reset() {
            currentData = null;
            currentHeaders = [];
            generatedSQL = [];
            document.getElementById('previewSection').classList.remove('active');
            document.getElementById('sqlOutput').style.display = 'none';
            document.getElementById('fileInput').value = '';
            document.getElementById('statusMessage').style.display = 'none';
            document.getElementById('progressBar').style.display = 'none';
            document.getElementById('statsCard').style.display = 'none';
            document.getElementById('campaignList').style.display = 'none';
            document.getElementById('warningBox').style.display = 'none';
            showStatus('Reset complete. Ready for new file.', 'info');
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', () => {
            loadExistingListIds();
            
            const uploadSection = document.getElementById('uploadSection');
            const fileInput = document.getElementById('fileInput');
            const selectFileBtn = document.getElementById('selectFileBtn');
            const generateSqlBtn = document.getElementById('generateSqlBtn');
            const resetBtn = document.getElementById('resetBtn');
            const copySqlBtn = document.getElementById('copySqlBtn');
            const downloadSqlBtn = document.getElementById('downloadSqlBtn');
            const campaignSelect = document.getElementById('campaignSelect');
            
            uploadSection.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadSection.classList.add('drag-over');
            });
            
            uploadSection.addEventListener('dragleave', () => {
                uploadSection.classList.remove('drag-over');
            });
            
            uploadSection.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadSection.classList.remove('drag-over');
                const file = e.dataTransfer.files[0];
                if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv'))) {
                    parseFile(file);
                } else {
                    showStatus('Please upload a valid Excel or CSV file', 'error');
                }
            });
            
            selectFileBtn.addEventListener('click', () => {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', (e) => {
                if (e.target.files[0]) {
                    parseFile(e.target.files[0]);
                }
            });
            
            campaignSelect.addEventListener('change', () => {
                updateCampaigns();
            });
            
            if (generateSqlBtn) generateSqlBtn.addEventListener('click', generateSQL);
            if (resetBtn) resetBtn.addEventListener('click', reset);
            if (copySqlBtn) copySqlBtn.addEventListener('click', copyToClipboard);
            if (downloadSqlBtn) downloadSqlBtn.addEventListener('click', downloadSQL);
            
            showStatus('Ready to upload leads. Select an Excel or CSV file to begin.', 'info');
        });
    </script>
</body>
</html>