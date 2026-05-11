<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>FORM XII - Register of Contractors</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 6px;
        }

        .form-container {
            border: 2px solid black;
            padding: 8px;
            margin: 10px auto;
            width: 92%;
        }

        .form-header {
            text-align: center;
            margin-bottom: 6px;
            line-height: 1.2;
            padding: 4px 0;
        }

        .form-header div {
            margin: 1px 0;
        }

        .header-title {
            font-weight: bold;
            font-size: 13px;
        }

        .header-rule {
            font-size: 9px;
        }

        .info-section {
            margin-bottom: 8px;
            font-size: 9px;
        }

        .info-row {
            margin-bottom: 4px;
            display: flex;
            align-items: center;
        }

        .info-label {
            font-weight: bold;
            width: 40%;
            margin-right: 5px;
        }



        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 9px;
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 3px 4px;
            font-size: 9px;
            line-height: 1.2;
            text-align: center;
            vertical-align: middle;
        }

        .register-table th {
            font-weight: bold;
            background-color: #fff;
            word-wrap: break-word;
        }

        .register-table td {
            height: 16px;
            font-size: 8px;
        }

        .col-1 {
            width: 5%;
        }

        .col-2 {
            width: 20%;
            text-align: left;
        }

        .col-3 {
            width: 15%;
            text-align: left;
        }

        .col-4 {
            width: 15%;
            text-align: left;
        }

        .col-5 {
            width: 10%;
        }

        .col-6 {
            width: 10%;
        }

        .col-7 {
            width: 10%;
            text-align: center;
        }

        .footer-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-bottom: 10px;
            font-size: 9px;
        }

        .footer-left {
            flex: 1;
        }

        .footer-right {
            flex: 1;
            text-align: right;
        }



        .signature-label {
            margin-top: 6px;
            font-weight: bold;
            font-size: 9px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <div class="form-header">
            <div class="header-title">FORM XII</div>
            <div class="header-rule">[See Rule 74]</div>
            <div class="header-title">Register of Contractors</div>
        </div>

        <div class="info-section">
            <div style="margin-bottom: 4px; font-size: 9px;">
                <div style="font-weight: bold; margin-bottom: 2px;">Name and address of the Principal Employer</div>
                <div style="margin-left: 0;">{{ data_get($header, 'tenant.name') ?? '' }}</div>
            </div>

            <div style="margin-bottom: 4px; font-size: 9px;">
                <div style="font-weight: bold; margin-bottom: 2px;">Name and address of the Establishment</div>
                <div style="margin-left: 0;">{{ data_get($header, 'branch.address') ?? '' }}</div>
            </div>
        </div>

        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-1">SL. No.</th>
                    <th class="col-2">Name and address of contractor</th>
                    <th class="col-3">Nature of work on contract</th>
                    <th class="col-4">Location of contract work</th>
                    <th colspan="2" style="text-align: center;">Period of contract</th>
                    <th class="col-7">Maximum No. of workmen employed by contractor</th>
                </tr>
                <tr>
                    <th class="col-1">1</th>
                    <th class="col-2">2</th>
                    <th class="col-3">3</th>
                    <th class="col-4">4</th>
                    <th class="col-5">5<br>(From)</th>
                    <th class="col-6">6<br>(To)</th>
                    <th class="col-7">7</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($rows) && count($rows) > 0)
                    @foreach ($rows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $row['contractor_name'] ?? '' }}
                                @if(!empty($row['contractor_address']))<br>{{ $row['contractor_address'] }}@endif
                            </td>
                            <td>{{ $row['nature_of_work'] ?? '' }}</td>
                            <td>{{ $row['work_location'] ?? '' }}</td>
                            <td>{{ $row['contract_from'] ?? '' }}</td>
                            <td>{{ $row['contract_to'] ?? '' }}</td>
                            <td>{{ $row['max_workers'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr class="no-records-row">
                        <td colspan="7" style="height:32px; text-align:center; vertical-align:middle; font-weight:bold;">NIL</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer-section">
            <div class="footer-left">
                <div>Place:</div>
                <div>Date:</div>
            </div>
            <div class="footer-right">
                <div class="signature-label" style="margin-top: 0;">Signature of the Licensing Officer</div>
            </div>
        </div>
    </div>
</body>

</html>
