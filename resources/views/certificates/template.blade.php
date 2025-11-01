<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Georgia', 'Times New Roman', serif;
            background: #f5f7fa;
        }
        .certificate {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: #667eea;
            page-break-after: avoid;
            page-break-inside: avoid;
        }
        .certificate-inner {
            position: absolute;
            top: 5mm;
            left: 5mm;
            right: 5mm;
            bottom: 5mm;
            background: white;
            border: 3mm double #667eea;
            padding: 15mm 30mm 20mm 30mm;
        }
        .header {
            text-align: center;
            margin-bottom: 5mm;
            position: relative;
        }
        .logo {
            font-size: 24pt;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 2pt;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 9pt;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 2pt;
            margin-top: 2mm;
            font-weight: 300;
        }
        .seal {
            position: absolute;
            top: -10mm;
            right: 20mm;
            width: 20mm;
            height: 20mm;
            border: 2.5mm solid #667eea;
            border-radius: 50%;
            background: #667eea;
            text-align: center;
            padding-top: 7mm;
        }
        .seal-text {
            color: white;
            font-size: 8pt;
            font-weight: bold;
            line-height: 1.4;
            display: block;
        }
        .certificate-title {
            text-align: center;
            font-size: 28pt;
            color: #2d3748;
            margin: 5mm 0 3mm;
            letter-spacing: 2pt;
            line-height: 1.2;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1pt solid #e2e8f0;
            padding-bottom: 3mm;
        }
        .certificate-subtitle {
            text-align: center;
            font-size: 10pt;
            color: #718096;
            margin: 3mm 0;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            font-weight: 300;
        }
        .recipient {
            text-align: center;
            margin: 4mm 0;
        }
        .recipient-name {
            font-size: 26pt;
            color: #667eea;
            font-weight: bold;
            border-bottom: 3pt double #667eea;
            display: inline-block;
            padding: 0 20mm 3mm;
            line-height: 1.2;
            font-style: italic;
        }
        .completion-text {
            text-align: center;
            font-size: 11pt;
            color: #4a5568;
            line-height: 1.6;
            margin: 4mm auto;
            max-width: 200mm;
            font-style: italic;
        }
        .course-name {
            font-size: 18pt;
            color: #667eea;
            font-weight: bold;
            margin: 4mm 0;
            line-height: 1.4;
            text-transform: uppercase;
            letter-spacing: 1pt;
            display: block;
            padding: 2mm 0;
        }
        .details {
            width: 100%;
            margin: 6mm 0 4mm;
            background: #f7fafc;
            border-radius: 2mm;
            padding: 4mm 0;
            border: 1pt solid #e2e8f0;
        }
        .detail-row {
            width: 100%;
            text-align: center;
        }
        .detail-cell {
            display: inline-block;
            width: 32%;
            padding: 2mm 3mm;
            text-align: center;
            vertical-align: top;
            border-right: 1pt solid #e2e8f0;
        }
        .detail-cell:last-child {
            border-right: none;
        }
        .detail-label {
            font-size: 9pt;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1pt;
            line-height: 1.2;
            font-weight: 600;
            display: block;
            margin-bottom: 2mm;
        }
        .detail-value {
            font-size: 12pt;
            color: #2d3748;
            font-weight: bold;
            line-height: 1.2;
            display: block;
        }
        .signatures {
            width: 100%;
            margin-top: 8mm;
            text-align: center;
        }
        .signature {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 5mm;
        }
        .signature-line {
            border-top: 2pt solid #2d3748;
            margin-bottom: 2mm;
            width: 60mm;
            margin-left: auto;
            margin-right: auto;
        }
        .signature-title {
            font-size: 9pt;
            color: #718096;
            text-transform: uppercase;
            line-height: 1.2;
            letter-spacing: 1pt;
            margin-bottom: 1mm;
            font-weight: 600;
        }
        .signature-name {
            font-size: 11pt;
            color: #2d3748;
            font-weight: bold;
            line-height: 1.2;
            margin-top: 1mm;
        }
        .footer {
            position: absolute;
            bottom: 8mm;
            left: 35mm;
            right: 35mm;
            text-align: center;
            border-top: 1pt solid #e2e8f0;
            padding-top: 2mm;
        }
        .certificate-number {
            font-size: 8pt;
            color: #a0aec0;
            letter-spacing: 1pt;
            line-height: 1.2;
            font-weight: 600;
        }
        .verification-url {
            font-size: 8pt;
            color: #667eea;
            margin-top: 1mm;
            line-height: 1.2;
            font-weight: 500;
        }
        .security-text {
            font-size: 7pt;
            color: #cbd5e0;
            margin-top: 1mm;
            font-style: italic;
        }
        .decorative-corner {
            position: absolute;
            width: 15mm;
            height: 15mm;
            border: 2mm solid #667eea;
        }
        .corner-tl {
            top: 8mm;
            left: 8mm;
            border-right: none;
            border-bottom: none;
        }
        .corner-tr {
            top: 8mm;
            right: 8mm;
            border-left: none;
            border-bottom: none;
        }
        .corner-bl {
            bottom: 8mm;
            left: 8mm;
            border-right: none;
            border-top: none;
        }
        .corner-br {
            bottom: 8mm;
            right: 8mm;
            border-left: none;
            border-top: none;
        }
        .qr-code {
            position: absolute;
            bottom: 10mm;
            right: 18mm;
            width: 22mm;
            height: 22mm;
            background: white;
            padding: 2mm;
            border: 2pt solid #667eea;
            border-radius: 2mm;
        }
        .qr-code img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .qr-label {
            position: absolute;
            bottom: 4mm;
            right: 18mm;
            width: 22mm;
            text-align: center;
            font-size: 6pt;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="certificate-inner">
            <div class="decorative-corner corner-tl"></div>
            <div class="decorative-corner corner-tr"></div>
            <div class="decorative-corner corner-bl"></div>
            <div class="decorative-corner corner-br"></div>
            
            <div class="header">
                <div class="logo">Mini LMS</div>
                <div class="subtitle">Professional Learning Management System</div>
                <div class="seal">
                    <span class="seal-text">VERIFIED</span>
                    <span class="seal-text">CERTIFICATE</span>
                </div>
            </div>
            
            <div class="certificate-title">Certificate of Completion</div>
            <div class="certificate-subtitle">This is to certify that</div>
            
            <div class="recipient">
                <div class="recipient-name">{{ $certificate->user->name }}</div>
            </div>
            
            <div class="completion-text">
                has successfully completed the comprehensive course
                <span class="course-name">{{ $certificate->course->title }}</span>
                demonstrating exceptional dedication, commitment, and mastery of the subject matter,<br>
                meeting all requirements and achieving the highest standards of excellence.
            </div>
            
            <div class="details">
                <div class="detail-row">
                    <div class="detail-cell">
                        <span class="detail-label">Course Level</span>
                        <span class="detail-value">{{ $certificate->course->level }}</span>
                    </div>
                    <div class="detail-cell">
                        <span class="detail-label">Completion Date</span>
                        {{-- {{dd($certificate)}} --}}
                        <span class="detail-value">{{ $certificate->issued_at }}</span>
                    </div>
                    <div class="detail-cell">
                        <span class="detail-label">Certificate ID</span>
                        <span class="detail-value">{{ $certificate->certificate_number }}</span>
                    </div>
                </div>
            </div>
            
            <div class="signatures">
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-title">Course Instructor</div>
                    <div class="signature-name">{{ $certificate->course->creator->name ?? 'Course Instructor' }}</div>
                </div>
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-title">Authorized By</div>
                    <div class="signature-name">{{ $certificate->issuer->name ?? 'Platform Administrator' }}</div>
                </div>
            </div>
            
            <div class="footer">
                <div class="certificate-number">CERTIFICATE NO: {{ $certificate->certificate_number }}</div>
                <div class="verification-url">Verify Authenticity: {{ $certificate->verification_url }}</div>
                <div class="security-text">This certificate is digitally signed and verifiable. Scan QR code to authenticate.</div>
            </div>
            
            <div class="qr-code">
                <img src="{{ $qrCodeDataUri }}" alt="QR Code">
            </div>
            <div class="qr-label">Scan to Verify</div>
        </div>
    </div>
</body>
</html>