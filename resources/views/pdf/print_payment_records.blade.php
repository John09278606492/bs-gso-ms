<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student's Payment Information</title>
</head>
<body style="font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; padding: 0.5rem; background-color: #fff;">

    @php
        $totalPayments = $payments->where('status1', 'paid')->sum('amount');
        $totalRefunded = $payments->where('status1', 'refunded')->sum('amount');
    @endphp

    <div style="width: 100%; margin-bottom: 100px;">
        <!-- Left Logo -->
        <div style="float: left; width: 17%; text-align: left;">
            <img src="{{ public_path('images/bisu logo2.png') }}" alt="BISU Logo" style="height: 6rem;">
        </div>

        <!-- Text in the Center -->
        <div style="float: left; width: 33%; text-align: left; margin-top: 10px">
            <h3 style="margin: 0;">Republic of the Philippines</h3>
            <h3 style="margin: 0;">BOHOL ISLAND STATE UNIVERSITY</h3>
            <h3 style="margin: 0;">San Isidro, Calape, Bohol</h3>
            <h4 style="margin: 0;">Parents, Teachers, Guardians & Employees Association</h4>
            <p style="margin: 0;">Balance | Integrity | Stewardship | Uprightness</p>
        </div>

        <!-- Right Logo -->
        <div style="float: left; width: 23%; text-align: center;">
            <img src="{{ public_path('images/bagong_pilipinas.png') }}" alt="BISU Logo" style="height: 6rem;">
        </div>
        <div style="float: left; width: 23%; text-align: center;">
            <img src="{{ public_path('images/tuv logo.png') }}" alt="BISU Logo" style="height: 6rem;">
        </div>
    </div>


    <div style="width: 100%; margin: 0 auto; padding: 0.5rem; background-color: #fff; border-radius: 0.5rem;">
        <h2 style="margin-bottom: 1rem; font-size: 20px; text-align: center; font-weight: bold;">Student Payment Records</h2>

        <p style="font-size: 12px; font-weight: bold; text-align: left; margin-bottom: 1rem;">
            Date Range: {{ $date_from ?? 'N/A' }} - {{ $date_to ?? 'N/A' }}
        </p>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">#</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">Student IDN</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">Last Name</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">First Name</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">Middle Name</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">College</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">Program</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">Year Level</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">School Year</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">Date/Time</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">Status</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $index => $studentInfo)
                    <tr>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ $index + 1 }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->enrollment->stud)->studentidn }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->enrollment->stud)->lastname }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->enrollment->stud)->firstname }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->enrollment->stud)->middlename }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->enrollment->college)->college }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->enrollment->program)->program }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->enrollment->yearlevel)->yearlevel }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->enrollment->schoolyear)->schoolyear }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ $studentInfo->created_at->format('M d, Y - h:i a') }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ $studentInfo->status1 }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right;">₱{{ is_numeric($studentInfo->amount) ? (float)$studentInfo->amount : 0; }}</td>
                    </tr>
                @endforeach

                <!-- Summary rows -->
                <tr>
                    <td colspan="10" style="border: none;"></td>
                    <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">Total Amount Paid:</td>
                    <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">₱{{ number_format($totalPayments, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="10" style="border: none;"></td>
                    <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">Total Amount Refunded:</td>
                    <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">₱{{ number_format($totalRefunded, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
