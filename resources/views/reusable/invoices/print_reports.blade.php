<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students' Payment Information</title>
</head>
<body style="font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; padding: 0.5rem; background-color: #fff;">

    @php
        $totalCollections = $payments->sum(fn($payment) => $payment->collections->sum('amount'));
        $totalYearLevelPayments = $payments->sum(fn($payment) => $payment->yearlevelpayments->sum('amount'));
        $totalPays = $payments->sum(fn($payment) => $payment->pays->where('status1', 'paid')->sum('amount'))
                    - $payments->sum(fn($payment) => $payment->pays->where('status1', 'refunded')->sum('amount'));

        $remainingBalance = ($totalCollections + $totalYearLevelPayments) - $totalPays;
        $totalRefundable = $payments->sum(fn($refund) => $refund->refundable_amount);
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
        <h2 style="margin-bottom: 1rem; font-size: 20px; text-align: center; font-weight: bold;">Student Payment Information</h2>

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
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">Total Amount Paid</th>
                    <th style="padding: 0.25rem; border: 1px solid #ccc; text-align: right;">Total Remaining Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $index => $studentInfo)
                    <tr>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ $index + 1 }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->stud)->studentidn }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->stud)->lastname }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->stud)->firstname }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->stud)->middlename }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->college)->college }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->program)->program }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->yearlevel)->yearlevel }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: left;">{{ optional($studentInfo->schoolyear)->schoolyear }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right;">₱{{ is_numeric($studentInfo->payments) ? (float)$studentInfo->payments : 0; }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right;">
                            @if(is_numeric($studentInfo->balance))
                                @if($studentInfo->balance < 0)
                                    Refundable Amount: ₱{{ number_format(abs($studentInfo->balance), 2) }}
                                @else
                                    ₱{{ number_format($studentInfo->balance, 2) }}
                                @endif
                            @else
                                ₱0.00
                            @endif
                        </td>
                    </tr>
                @endforeach

                <!-- Summary rows -->
                <tr>
                    <td colspan="8" style="border: none;"></td>
                    <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">Grand Total:</td>
                    <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">₱{{ number_format($totalPays, 2) }}</td>
                    <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">
                        ₱{{ number_format(max(0, $remainingBalance), 2) }}
                    </td>
                </tr>

                <!-- Grand Total Refundable Amount -->
                <tr>
                    <td colspan="8" style="border: none;"></td>
                    <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">Grand Total Refundable Amount:</td>
                    <td colspan="2" style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">₱{{ number_format($totalRefundable, 2) }}</td>
                </tr>

                <!-- Overall Total Expected Amount -->
                <tr>
                    <td colspan="8" style="border: none;"></td>
                    <td style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">Overall Total Expected Amount:</td>
                    <td colspan="2" style="padding: 0.25rem; border: 1px solid #ccc; text-align: right; font-weight: bold;">₱{{ number_format($totalPays + $remainingBalance, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
