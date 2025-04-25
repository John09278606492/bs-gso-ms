<style>
    @page {
        size: 89mm auto; /* Set the width to match your container width */
        margin: 0; /* Remove default margins for a snug fit */
    }
    body {
        margin: 0;
        padding: 0;
    }
</style>
@php
    $paidAmount = $payments->pays->where('status1', 'paid')->sum('amount');
    $refundedAmount = $payments->pays->where('status1', 'refunded')->sum('amount');
    $totalPays = $paidAmount - $refundedAmount;

    $totalCollections = 0;
    $totalYearLevelPayments = 0;
    $allFees = collect();

    $schoolYear = $payments->schoolyear->schoolyear ?? 'N/A';

    foreach ($payments->yearlevelpayments as $fee) {
        $allFees->push([
            'type' => 'Year Level Fee',
            'description' => $fee->description . ' - Year Level ' . optional($fee->yearlevel)->yearlevel,
            'amount' => $fee->amount,
            'schoolyear' => $schoolYear
        ]);
        $totalYearLevelPayments += $fee->amount;
    }

    foreach ($payments->collections as $fee) {
        $allFees->push([
            'type' => 'School Year Fee',
            'description' => $fee->description . ' - Semester ' . optional($fee->semester)->semester,
            'amount' => $fee->amount,
            'schoolyear' => $schoolYear
        ]);
        $totalCollections += $fee->amount;
    }

    $groupedFees = $allFees->groupBy('schoolyear');
    $remainingBalance = ($totalCollections + $totalYearLevelPayments) - $totalPays;
@endphp

<head>
    <title>Print Invoice</title>
</head>
<div style="margin: 0 auto; padding: 0.5rem; background-color: #fff; border-radius: 0.5rem; color: #333; font-family: 'DejaVu Sans', sans-serif; font-size: 11px;">
    <h1 style="margin-bottom: 0.5rem; font-size: 16px; font-weight: bold; text-align: right; color: #000; line-height: 1.2;">INVOICE</h1>
    <p style="margin-bottom: 0.25rem; text-align: right; font-size: 10px; line-height: 0.5;">BISU Calape GSO MS</p>

    <div style="padding: 0.10rem 0.75rem; line-height: .80;">
        <h2 style="margin-bottom: 0.5rem; font-size: 13px; font-weight: bold;">Student Information</h2>
        <div style="margin-bottom: 0.25rem; display: flex;">
            <span style="width: 110px; font-weight: bold;">Name:</span>
            <span>{{ $payments->stud->firstname }} {{ $payments->stud->middlename }} {{ $payments->stud->lastname }}</span>
        </div>
        <div style="margin-bottom: 0.25rem; display: flex;">
            <span style="width: 110px; font-weight: bold;">Student IDN:</span>
            <span>{{ $payments->stud->studentidn }}</span>
        </div>
        <div style="margin-bottom: 0.25rem; display: flex;">
            <span style="width: 110px; font-weight: bold;">College:</span>
            <span>{{ $payments->college->college }}</span>
        </div>
        <div style="margin-bottom: 0.25rem; display: flex;">
            <span style="width: 110px; font-weight: bold;">Program:</span>
            <span>{{ $payments->program->program }}</span>
        </div>
        <div style="margin-bottom: 0.25rem; display: flex;">
            <span style="width: 110px; font-weight: bold;">Year Level:</span>
            <span>{{ $payments->yearlevel->yearlevel }}</span>
        </div>
        <div style="margin-bottom: 0rem; display: flex;">
            <span style="width: 110px; font-weight: bold;">School Year:</span>
            <span>{{ $payments->schoolyear->schoolyear }}</span>
        </div>
    </div>

    <div style="padding: 0.10rem 0.75rem; margin-bottom: 0rem; line-height: .80;">
        <h2 style="margin-bottom: 0.5rem; font-size: 13px; font-weight: bold;">Siblings Information</h2>
        <table style="width: 100%; margin-bottom: 0rem; border-collapse: collapse; border: 1px solid #ccc; font-size: 11px;">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Complete Name</th>
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">College</th>
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Program</th>
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Year Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($siblingsInformation->siblings as $siblingsInfo)
                    <tr>
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">{{ $siblingsInfo->stud->firstname }} {{ $siblingsInfo->stud->middlename }} {{ $siblingsInfo->stud->lastname }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">{{ $siblingsInfo->stud->enrollments->college->college }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">{{ $siblingsInfo->stud->enrollments->program->program }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">{{ $siblingsInfo->stud->enrollments->yearlevel->yearlevel }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding: 0rem; text-align: center; border: 1px solid #ccc;">No sibling/s found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- <div style="padding: 0.10rem 0.75rem; line-height: .80;">
        <h2 style="margin-bottom: 0.5rem; font-size: 15px; font-weight: bold;">Year Level Fee Type</h2>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc; font-size: 11px;">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Description</th>
                    <th style="padding: 0.25rem; text-align: right; border: 1px solid #ccc;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments->yearlevelpayments as $fee)
                    <tr>
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">{{ $fee->description }}</td>
                        <td style="padding: 0.25rem; text-align: right; border: 1px solid #ccc;">₱{{ number_format($fee->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="padding: 0.25rem; text-align: center; border: 1px solid #ccc;">No year level fee type/s available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="text-align: right; margin-top: 0.5rem; font-size: 11px; font-weight: bold;">Total: ₱{{ number_format($payments->yearlevelpayments->sum('amount'), 2) }}</div>
    </div> --}}

    <div style="padding: 0.10rem 0.75rem; line-height: .80;">
        <h2 style="margin-bottom: 0.5rem; font-size: 13px; font-weight: bold;">Fund Type</h2>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc; font-size: 11px;">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Description</th>
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Status</th>
                    <th style="padding: 0.25rem; text-align: right; border: 1px solid #ccc;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPaid = 0;
                    $totalUnpaid = 0;
                @endphp

                @forelse ($payments->collections as $fee)
                    @php
                        $status = strtolower(optional($fee->pivot)->collection_status ?? 'unpaid');
                        $amount = $fee->amount ?? 0;

                        if ($status === 'paid') {
                            $totalPaid += $amount;
                            $rowColor = 'green';
                        } else {
                            $totalUnpaid += $amount;
                            $rowColor = 'red';
                        }
                    @endphp
                    <tr style="color: {{ $rowColor }};">
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">
                            {{ $fee->description }} - Semester {{ $fee->semester->semester ?? '-' }}
                        </td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">
                            {{ ucfirst($status) }}
                        </td>
                        <td style="padding: 0.25rem; text-align: right; border: 1px solid #ccc;">
                            ₱{{ number_format($amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding: 0.25rem; text-align: center; border: 1px solid #ccc;">
                            No school year fee type/s available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="text-align: right; margin-top: 0.75rem; font-size: 9px; font-weight: semi-bold;">
            Total Amount Paid: ₱{{ number_format($totalPaid, 2) }}
        </div>
        <div style="text-align: right; margin-top: 0.25rem; font-size: 9px; font-weight: semi-bold;">
            Total Amount Unpaid: ₱{{ number_format($totalUnpaid, 2) }}
        </div>
        <div style="text-align: right; margin-top: 0.25rem; font-size: 11px; font-weight: bold;">
            Total Amount Due: ₱{{ number_format($totalPaid + $totalUnpaid, 2) }}
        </div>
    </div>


    {{-- <div style="padding: 0.10rem 0.75rem; line-height: .80;">
        <h2 style="margin-bottom: 0.5rem; font-size: 13px; font-weight: bold;">Fund Type</h2>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #ccc; font-size: 11px;">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Description</th>
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Status</th>
                    <th style="padding: 0.25rem; text-align: right; border: 1px solid #ccc;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPaid = 0;
                    $totalUnpaid = 0;
                @endphp

                @forelse ($payments->collections as $fee)
                    @php
                        $collectionStatus = optional($fee->pivot)->collection_status;
                        $rowColor = is_null($collectionStatus) ? 'red' : 'green';

                        if (strtolower($collectionStatus) === 'paid') {
                            $totalPaid += $fee->amount;
                        } else {
                            $totalUnpaid += $fee->amount;
                        }
                    @endphp
                    <tr style="color: {{ $rowColor }};">
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">
                            {{ $fee->description }} - Semester {{ $fee->semester->semester ?? '-' }}
                        </td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">
                            {{ $collectionStatus ?? 'unpaid' }}
                        </td>
                        <td style="padding: 0.25rem; text-align: right; border: 1px solid #ccc;">
                            ₱{{ number_format($fee->amount ?? 0, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding: 0.25rem; text-align: center; border: 1px solid #ccc;">
                            No school year fee type/s available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="text-align: right; margin-top: 0.75rem; font-size: 9px; font-weight: semi-bold;">
            Total Amount Paid: ₱{{ number_format($totalPaid, 2) }}
        </div>
        <div style="text-align: right; margin-top: 0.25rem; font-size: 9px; font-weight: semi-bold;">
            Total Amount Unpaid: ₱{{ number_format($totalUnpaid, 2) }}
        </div>
        <div style="text-align: right; margin-top: 0.25rem; font-size: 11px; font-weight: bold;">
            Total Amount Due: ₱{{ number_format($totalPaid + $totalUnpaid, 2) }}
        </div>
    </div> --}}


    <div style="padding: 0.10rem 0.75rem; line-height: .80;">
        <h2 style="margin-bottom: 0.5rem; font-size: 13px; font-weight: bold;">Payment History</h2>
        <table style="width: 100%; margin-bottom: 0.75rem; border-collapse: collapse; border: 1px solid #ccc; font-size: 11px;">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Date</th>
                    <th style="padding: 0.25rem; text-align: left; border: 1px solid #ccc;">Status</th>
                    <th style="padding: 0.25rem; text-align: right; border: 1px solid #ccc;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments->pays as $pay)
                    <tr>
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">{{ $pay->created_at->format('M d, Y / h:i:s a') }}</td>
                        <td style="padding: 0.25rem; border: 1px solid #ccc;">{{ $pay->status1 }}</td>
                        <td style="padding: 0.25rem; text-align: right; border: 1px solid #ccc;">₱{{ number_format($pay->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding: 0.25rem; text-align: center; border: 1px solid #ccc;">No payment history found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="text-align: right; margin-top: 0rem; font-size: 11px; font-weight: bold;">Total: ₱{{ number_format($payments->pays->sum('amount'), 2) }}</div>
    </div>

    @php
    $isNegative = $remainingBalance < 0;
    @endphp

    <div style="padding: 0.5rem 0.75rem;">
        <hr style="margin: 10px 0; border-top: 1px solid #ccc;" />
        @if ($isNegative)
            <div style="text-align: right; font-size: 14px; font-weight: bold; color: green;">Fully Paid!</div>
            <div style="text-align: right; font-size: 14px; font-weight: bold; color: goldenrod;">Refundable Balance: ₱{{ number_format(abs($remainingBalance), 2) }}</div>
        @elseif ($remainingBalance == 0)
            <div style="text-align: right; font-size: 14px; font-weight: bold; color: green;">Fully Paid!</div>
        @else
            <div style="text-align: right; font-size: 14px; font-weight: bold; color: red;">Remaining Balance: ₱{{ number_format($remainingBalance, 2) }}</div>
        @endif
    </div>

</div>
{{-- @php
    $totalCollections = $payments->collections->sum('amount');
    $totalYearLevelPayments = $payments->yearlevelpayments->sum('amount');
    $totalPays = $payments->pays->sum('amount');
    $remainingBalance = ($totalCollections + $totalYearLevelPayments) - $totalPays;
@endphp

<style>
    @page {
        size: 1.39in 2.78in; /* Custom paper size in inches */
        margin: 0;
    }
    body {
        margin: 0;
        padding: 0;
        font-family: DejaVu Sans, sans-serif;
        font-size: 2px; /* Scaled down font size */
        color: #333;
    }
    .container {
        width: 1.3in; /* Adjusted to fit within paper size */
        margin: 0 auto;
        padding: 0.1rem;
        background: #fff;
        border-radius: 2px; /* Scaled down border radius */
    }
    h1, h2 {
        margin: 0;
        padding: 0;
        text-align: center;
        font-size: 2px; /* Scaled down font size */
        font-weight: bold;
    }
    h2 {
        margin-bottom: 0.1rem;
        text-align: left;
        font-size: 2px;
    }
    .info, .fees, .payments {
        margin-bottom: 0.1rem;
        line-height: 1;
    }
    .info div, .fees div {
        display: flex;
        justify-content: space-between;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0.1rem;
    }
    th, td {
        padding: 0.1rem;
        border: 0.5px solid #ccc; /* Thinner borders */
        text-align: left;
    }
    th {
        background: #f9f9f9;
        font-size: 2px; /* Scaled down font size */
    }
    td {
        font-size: 2px; /* Scaled down font size */
    }
    .summary {
        text-align: right;
        font-weight: bold;
        font-size: 2px; /* Scaled down font size */
    }
</style>

<div class="container">
    <h1>INVOICE</h1>
    <p style="text-align: center; font-size: 2px;">PTGEA, San Isidro, Calape, Bohol | 6328</p>
    <p style="text-align: center; font-size: 2px;">Contact: 0927 860 6492</p>

    <div class="info">
        <h2>Student Information</h2>
        <div><span>Name:</span> <span>{{ $payments->stud->firstname }} {{ $payments->stud->middlename }} {{ $payments->stud->lastname }}</span></div>
        <div><span>Student ID:</span> <span>{{ $payments->stud->studentidn }}</span></div>
        <div><span>College:</span> <span>{{ $payments->college->college }}</span></div>
        <div><span>Program:</span> <span>{{ $payments->program->program }}</span></div>
        <div><span>Year Level:</span> <span>{{ $payments->yearlevel->yearlevel }}</span></div>
        <div><span>School Year:</span> <span>{{ $payments->schoolyear->schoolyear }}</span></div>
    </div>

    <div class="fees">
        <h2>Siblings Information</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>College</th>
                    <th>Program</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($siblingsInformation->siblings as $sib)
                    <tr>
                        <td>{{ $sib->stud->firstname }} {{ $sib->stud->middlename }} {{ $sib->stud->lastname }}</td>
                        <td>{{ $sib->stud->enrollments->college->college }}</td>
                        <td>{{ $sib->stud->enrollments->program->program }}</td>
                        <td>{{ $sib->stud->enrollments->yearlevel->yearlevel }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">No sibling/s found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="fees">
        <h2>Year Level Fee</h2>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments->yearlevelpayments as $fee)
                    <tr>
                        <td>{{ $fee->description }}</td>
                        <td style="text-align: right;">₱{{ number_format($fee->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="text-align: center;">No year level fees.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="summary">Total: ₱{{ number_format($payments->yearlevelpayments->sum('amount'), 2) }}</div>
    </div>

    <div class="fees">
        <h2>School Year Fee</h2>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments->collections as $fee)
                    <tr>
                        <td>{{ $fee->description }} - Sem {{ $fee->semester->semester }}</td>
                        <td style="text-align: right;">₱{{ number_format($fee->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="text-align: center;">No school year fees.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="summary">Total: ₱{{ number_format($payments->collections->sum('amount'), 2) }}</div>
    </div>

    <div class="payments">
        <h2>Payment History</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments->pays as $pay)
                    <tr>
                        <td>{{ $pay->created_at->format('Y-m-d') }}</td>
                        <td style="text-align: right;">₱{{ number_format($pay->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="text-align: center;">No payment history found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="summary">Total Paid: ₱{{ number_format($payments->pays->sum('amount'), 2) }}</div>
    </div>

    <div class="summary">Remaining Balance: ₱{{ number_format($remainingBalance, 2) }}</div>
</div> --}}
