<style>
    @page {
        size: 350px auto; /* Set the width to match your container width */
        margin: 0; /* Remove default margins for a snug fit */
    }
    body {
        margin: 0;
        padding: 0;
    }
</style>
@php
    $totalCollections = 0;
    $totalYearLevelPayments = 0;
    $totalPays = $payments->pluck('pays')->flatten()
    ->where('status1', 'paid')->sum('amount')
    - $payments->pluck('pays')->flatten()
    ->where('status1', 'refunded')->sum('amount');
    $allFees = collect();

    foreach ($payments as $enrollment) {
        $schoolYear = $enrollment->schoolyear->schoolyear ?? 'N/A';

        foreach ($enrollment->yearlevelpayments as $fee) {
            $allFees->push([
                'type' => 'Year Level Fee',
                'description' => $fee->description . ' - Year Level ' . optional($fee->yearlevel)->yearlevel,
                'amount' => $fee->amount,
                'schoolyear' => $schoolYear
            ]);
            $totalYearLevelPayments += $fee->amount;
        }

        $totalPaid = 0;
        $totalUnpaid = 0;
        $totalCollections = 0;

        foreach ($enrollment->collections as $fee) {
            // Push the fee data into the collection
            $allFees->push([
                'type' => 'School Year Fee',
                'description' => $fee->description . ' - Semester ' . optional($fee->semester)->semester,
                'amount' => $fee->amount,
                'collection_status' => $fee->pivot->collection_status ?? 'unpaid', // Default if null
                'schoolyear' => $schoolYear
            ]);

            // Accumulate the total collection amount
            $totalCollections += $fee->amount;

            // Calculate totalPaid and totalUnpaid based on collection status
            if ($fee->pivot->collection_status === 'paid') {
                $totalPaid += $fee->amount;
            } else {
                $totalUnpaid += $fee->amount;
            }
        }
    }

    $groupedFees = $allFees->groupBy('schoolyear');
    $remainingBalance = ($totalCollections + $totalYearLevelPayments) - $totalPays;
@endphp
<div style="padding: 10px; background-color: white; font-family: 'DejaVu Sans', sans-serif; font-size: 12px;">
    <h1 style="text-align: right; font-size: 16px; margin-bottom: 10px;">INVOICE</h1>
    <p style="margin-bottom: 0.25rem; text-align: right; font-size: 15px; line-height: 0.5;">BISU Calape GSO MS</p>

    <div style="margin-bottom: 10px;">
        <h2 style="font-size: 14px; margin-bottom: 5px;">Student Information</h2>
        @if ($payments->isNotEmpty())
            <div><strong>Name:</strong> {{ optional($payments->first()->stud)->firstname }} {{ optional($payments->first()->stud)->middlename }} {{ optional($payments->first()->stud)->lastname }}</div>
            <div><strong>Student IDN:</strong> {{ optional($payments->first()->stud)->studentidn }}</div>
        @endif
    </div>

    <div style="margin-bottom: 10px;">
        <h2 style="font-size: 14px; margin-bottom: 5px;">Fees Summary</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px; text-align: left;">Fee Type</th>
                    <th style="border: 1px solid #ccc; padding: 5px; text-align: left;">Description</th>
                    <th style="border: 1px solid #ccc; padding: 5px; text-align: left;">Status</th>
                    <th style="border: 1px solid #ccc; padding: 5px; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($groupedFees as $schoolYear => $fees)
                    <tr style="background-color: #e0e0e0;">
                        <td colspan="4" style="border: 1px solid #ccc; padding: 5px; font-weight: bold;">School Year: {{ $schoolYear }}</td>
                    </tr>
                    @foreach ($fees as $fee)
                        @php
                            $textColor = $fee['collection_status'] == 'paid' ? 'green' : 'red';
                        @endphp
                        <tr style="color: {{ $textColor }};">
                            <td style="border: 1px solid #ccc; padding: 5px;">{{ $fee['type'] }}</td>
                            <td style="border: 1px solid #ccc; padding: 5px;">{{ $fee['description'] }}</td>
                            <td style="border: 1px solid #ccc; padding: 5px;">{{ $fee['collection_status'] }}</td>
                            <td style="border: 1px solid #ccc; padding: 5px; text-align: right;">₱{{ number_format($fee['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="4" style="border: 1px solid #ccc; padding: 5px; text-align: center;">No fees available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Breakdown of Paid, Unpaid, and Total Amounts -->
        <div style="text-align: right; margin-top: 5px;">
            <div style="margin-bottom: 1px; font-size: 9px;">
                <strong>Total Amount Paid:</strong> ₱{{ number_format($totalPaid, 2) }}
            </div>
            <div style="margin-bottom: 1px; font-size: 9px;">
                <strong>Total Amount Unpaid:</strong> ₱{{ number_format($totalUnpaid, 2) }}
            </div>
            <div style="font-weight: bold; margin-top: 1px; font-size: 11px;">
                <strong>Total Amount Due:</strong> ₱{{ number_format($totalCollections + $totalYearLevelPayments, 2) }}
            </div>
        </div>
    </div>



    <div>
        <h2 style="font-size: 14px; margin-bottom: 5px;">Payment History</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #ccc; padding: 5px;">Date</th>
                    <th style="border: 1px solid #ccc; padding: 5px; text-align: right;">Status</th>
                    <th style="border: 1px solid #ccc; padding: 5px; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments->pluck('pays')->flatten() as $payment)
                    <tr>
                        <td style="border: 1px solid #ccc; padding: 5px;">{{ $payment->created_at->format('M d, Y h:i a') }}</td>
                        <td style="border: 1px solid #ccc; padding: 5px; text-align: right;">{{ ucfirst($payment->status1) }}</td>
                        <td style="border: 1px solid #ccc; padding: 5px; text-align: right;">₱{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="border: 1px solid #ccc; padding: 5px; text-align: center;">No payments recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="text-align: right; font-weight: bold; margin-top: 5px;">Total Amount Paid: ₱{{ number_format($totalPays, 2) }}</div>
        {{-- <div style="text-align: right; font-weight: bold; margin-top: 5px; font-size: 16px; color: {{ $remainingBalance > 0 ? 'red' : 'green' }};">
            Remaining Balance: ₱{{ number_format($remainingBalance, 2) }}
        </div> --}}
        @php
            $isNegative = $remainingBalance < 0;
        @endphp

        <hr style="margin: 20px 0; border-top: 2px solid #ccc;" />

        @if ($isNegative)
            <div style="text-align: right; font-family: 'DejaVu Sans', sans-serif; font-size: 16px; font-weight: bold; color: green;">
                Fully Paid!
            </div>
            <div style="text-align: right; font-family: 'DejaVu Sans', sans-serif; font-size: 16px; font-weight: bold; color: goldenrod;">
                Refundable Balance: ₱{{ number_format(abs($remainingBalance), 2) }}
            </div>
        @elseif ($remainingBalance == 0)
            <div style="text-align: right; font-family: 'DejaVu Sans', sans-serif; font-size: 16px; font-weight: bold; color: green;">
                Fully Paid!
            </div>
        @else
            <div style="text-align: right; font-family: 'DejaVu Sans', sans-serif; font-size: 16px; font-weight: bold; color: red;">
                Remaining Balance: ₱{{ number_format($remainingBalance, 2) }}
            </div>
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
