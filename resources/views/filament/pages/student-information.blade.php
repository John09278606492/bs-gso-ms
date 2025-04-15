<x-filament-panels::page>
    @if (!$studentInfo)
        <!-- Display message if no student found -->
        <p class="text-lg italic text-center text-gray-500">Student not found.</p>
    @else
        <!-- If studentInfo exists, display the UI -->
        <x-filament::tabs label="Content tabs">
            <x-filament::tabs.item
                :active="$activeTab === 'student info'"
                wire:click="$set('activeTab', 'student info')"
            >
                Student Info
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'enrollment'"
                wire:click="$set('activeTab', 'enrollment')"
            >
                Enrollment Info
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'siblings'"
                wire:click="$set('activeTab', 'siblings')"
            >
                Siblings
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTab === 'invoice'"
                wire:click="$set('activeTab', 'invoice')"
            >
                Invoice
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- Conditionally Render Sections --}}
        @if ($activeTab === 'student info')
            <x-filament::section>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-filament::fieldset>
                        <x-slot name="label">Student IDN</x-slot>
                        <span class="font-bold">{{ $studentInfo->studentidn }}</span>
                    </x-filament::fieldset>
                    <x-filament::fieldset>
                        <x-slot name="label">First Name</x-slot>
                        <span class="font-bold">{{ $studentInfo->firstname }}</span>
                    </x-filament::fieldset>
                    <x-filament::fieldset>
                        <x-slot name="label">Middle Name</x-slot>
                        <span class="font-bold">{{ $studentInfo->middlename }}</span>
                    </x-filament::fieldset>
                    <x-filament::fieldset>
                        <x-slot name="label">Last Name</x-slot>
                        <span class="font-bold">{{ $studentInfo->lastname }}</span>
                    </x-filament::fieldset>
                </div>
            </x-filament::section>
        @endif

        @if ($activeTab === 'enrollment')
        <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800 dark:text-gray-200">
            <h2 class="mb-4 text-sm font-semibold text-left dark:text-white">Student Information</h2>
            <div class="overflow-x-auto">
                <table class="w-full border border-collapse border-gray-300 min-w-max dark:border-gray-600">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                            <th class="p-2 text-xs text-left border border-gray-300 dark:border-gray-600 sm:text-sm">School Year</th>
                            <th class="p-2 text-xs text-left border border-gray-300 dark:border-gray-600 sm:text-sm">Semester</th>
                            <th class="p-2 text-xs text-left border border-gray-300 dark:border-gray-600 sm:text-sm">College</th>
                            <th class="p-2 text-xs text-left border border-gray-300 dark:border-gray-600 sm:text-sm">Program</th>
                            <th class="p-2 text-xs text-left border border-gray-300 dark:border-gray-600 sm:text-sm">Year Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($studentSchoolyear as $enrollment)
                            <tr class="dark:hover:bg-gray-700">
                                <td class="p-2 text-xs border border-gray-300 dark:border-gray-600 sm:text-sm">
                                    {{ optional($enrollment->schoolyear)->schoolyear ?? 'N/A' }}
                                </td>
                                <td class="p-2 text-xs border border-gray-300 dark:border-gray-600 sm:text-sm">
                                    {{ $enrollment->semesters->pluck('semester')->join(', ') ?? 'N/A' }}
                                </td>
                                <td class="p-2 text-xs border border-gray-300 dark:border-gray-600 sm:text-sm">
                                    {{ optional($enrollment->college)->college ?? 'N/A' }}
                                </td>
                                <td class="p-2 text-xs border border-gray-300 dark:border-gray-600 sm:text-sm">
                                    {{ optional($enrollment->program)->program ?? 'N/A' }}
                                </td>
                                <td class="p-2 text-xs border border-gray-300 dark:border-gray-600 sm:text-sm">
                                    {{ optional($enrollment->yearlevel)->yearlevel ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif


        @if ($activeTab === 'siblings')
            <x-filament::section>
                @if ($studentInfo->siblings->isEmpty())
                    <p class="text-sm italic text-center text-gray-500">No siblings found.</p>
                @else
                    <table class="w-full mb-6 border border-collapse border-gray-300 dark:border-gray-600">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                <th class="p-2 text-left border border-gray-300 dark:border-gray-600">First Name</th>
                                <th class="p-2 text-left border border-gray-300 dark:border-gray-600">Middle Name</th>
                                <th class="p-2 text-left border border-gray-300 dark:border-gray-600">Last Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($studentInfo->siblings as $siblingsInfo)
                                <tr class="dark:hover:bg-gray-700">
                                    <td class="p-2 border border-gray-300 dark:border-gray-600">{{ $siblingsInfo->stud->firstname }}</td>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600">{{ $siblingsInfo->stud->middlename }}</td>
                                    <td class="p-2 border border-gray-300 dark:border-gray-600">{{ $siblingsInfo->stud->lastname }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-filament::section>
        @endif

        @if ($activeTab === 'invoice')
            @if (!$payments || $payments->isEmpty())
                <p class="text-lg italic text-center text-gray-500">No records found for this student.</p>
            @else
                @php
                    $totalCollections = 0;
                    $totalYearLevelPayments = 0;
                    $totalPays = 0;
                    $allFees = collect();

                    foreach ($payments as $enrollment) {
                        $schoolYear = $enrollment->schoolyear->schoolyear;

                        foreach ($enrollment->yearlevelpayments as $fee) {
                            $allFees->push([
                                'type' => 'Year Level Fee',
                                'description' => $fee->description . ' - Year Level ' . $fee->yearlevel->yearlevel,
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


                        $totalPays += $enrollment->pays
                            ->where('status1', 'paid')
                            ->sum('amount')
                            - $enrollment->pays
                            ->where('status1', 'refunded')
                            ->sum('amount');
                    }

                    $groupedFees = $allFees->groupBy('schoolyear');
                    $remainingBalance = ($totalCollections + $totalYearLevelPayments) - $totalPays;
                @endphp

                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="selectedSchoolYear" wire:change="$refresh">
                        <option value="all">All</option>
                        @forelse ($schoolYears ?? [] as $sy)
                            <option value="{{ $sy->id }}" {{ $sy->id == $selectedSchoolYear ? 'selected' : '' }}>
                                {{ $sy->schoolyear }}
                            </option>
                        @empty
                            <option value="">No school years available</option>
                        @endforelse
                    </x-filament::input.select>
                </x-filament::input.wrapper>


                <div class="flex justify-end mt-4">
                    <a href="{{ route('PRINT.INVOICE.DOWNLOAD', ['id' => $payments->first()->id, 'schoolYear' => $selectedSchoolYear ?? 'all']) }}">
                        <x-filament::button size="sm" class="w-auto" icon="heroicon-m-arrow-down-tray" color="danger">
                            Download PDF
                        </x-filament::button>
                    </a>
                </div>

                <div class="p-3 bg-white rounded-lg shadow sm:p-6 dark:bg-gray-800 dark:text-gray-200">
                    <!-- Invoice Header -->
                    <h1 class="mb-4 text-xl font-bold text-right sm:mb-6 sm:text-2xl dark:text-white">INVOICE</h1>
                    <p style="margin-bottom: 0.25rem; text-align: right; font-size: 15px; line-height: 0.5;">BISU Calape GSO MS</p>

                    <!-- Student Information -->
                    <div class="p-3 mb-4 sm:p-4 sm:mb-6">
                        <h2 class="mb-3 text-lg font-semibold sm:mb-4 sm:text-xl dark:text-white">Student Information</h2>

                        <div class="flex flex-col mb-2 sm:flex-row sm:items-center">
                            <span class="w-32 font-medium">Name:</span>
                            <strong class="flex-1 text-left">{{ $payments->first()->stud->firstname }} {{ $payments->first()->stud->middlename }} {{ $payments->first()->stud->lastname }}</strong>
                        </div>

                        <div class="flex flex-col mb-2 sm:flex-row sm:items-center">
                            <span class="w-32 font-medium">Student IDN:</span>
                            <strong class="flex-1 text-left">{{ $payments->first()->stud->studentidn }}</strong>
                        </div>
                    </div>

                    <!-- Fees Summary -->
                    <div class="p-3 mb-4 sm:p-4 sm:mb-6">
                        <h2 class="mb-3 text-lg font-semibold text-left sm:mb-4 sm:text-xl dark:text-white">Fees Summary</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full mb-4 border border-collapse border-gray-300 sm:mb-6 dark:border-gray-600">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                        <th class="p-2 text-left border border-gray-300 dark:border-gray-600">Fee Type</th>
                                        <th class="p-2 text-left border border-gray-300 dark:border-gray-600">Description</th>
                                        <th class="p-2 text-left border border-gray-300 dark:border-gray-600">Status</th>
                                        <th class="p-2 text-right border border-gray-300 dark:border-gray-600">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($groupedFees as $schoolYear => $fees)
                                        <tr class="bg-gray-200 dark:bg-gray-800">
                                            <td colspan="4" class="p-2 text-base font-bold text-left border border-gray-300 sm:text-lg dark:border-gray-600">
                                                School Year: {{ $schoolYear }}
                                            </td>
                                        </tr>
                                        @foreach ($fees as $fee)
                                            @php
                                                $collectionStatus = $fee['collection_status'] ?? null;
                                                $rowClass = is_null($collectionStatus) || $collectionStatus !== 'paid'
                                                    ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                    : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                            @endphp

                                            <tr class="{{ $rowClass }}">
                                                <td class="p-2 border border-gray-300 dark:border-gray-600 min-w-[100px]">
                                                    {{ $fee['type'] }}
                                                </td>
                                                <td class="p-2 border border-gray-300 dark:border-gray-600">
                                                    {{ $fee['description'] }}
                                                </td>
                                                <td class="p-2 border border-gray-300 dark:border-gray-600">
                                                    {{ $collectionStatus ?? 'unpaid' }}
                                                </td>
                                                <td class="p-2 text-right border border-gray-300 dark:border-gray-600 whitespace-nowrap">
                                                    ₱{{ number_format($fee['amount'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @empty
                                        <tr>
                                            <td colspan="3" class="p-2 text-center border border-gray-300 dark:border-gray-600 dark:text-gray-400">
                                                No fees available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Breakdown of Paid, Unpaid, and Total Amounts -->
                        <div class="w-full mt-2 text-[9px] text-right dark:text-gray-100">
                            <div class="grid grid-cols-2 px-2 py-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div>Total Amount Paid:</div>
                                <div class="text-right">₱{{ number_format($totalPaid, 2) }}</div>
                            </div>
                            <div class="grid grid-cols-2 px-2 py-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div>Total Amount Unpaid:</div>
                                <div class="text-right">₱{{ number_format($totalUnpaid, 2) }}</div>
                            </div>
                            <div class="grid grid-cols-2 px-2 py-1 text-base font-bold border border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <div>Total Amount Due:</div>
                                <div class="text-right">₱{{ number_format($totalCollections + $totalYearLevelPayments, 2) }}</div>
                            </div>
                        </div>

                    </div>

                    <!-- Payment History -->
                    <div class="p-3 mb-4 sm:p-4 sm:mb-6">
                        <h2 class="mb-3 text-lg font-semibold text-left sm:mb-4 sm:text-xl dark:text-white">Payment History</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full mb-4 border border-collapse border-gray-300 sm:mb-6 dark:border-gray-600">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                                        <th class="p-2 border border-gray-300 dark:border-gray-600">Date</th>
                                        <th class="p-2 text-right border border-gray-300 dark:border-gray-600">Status</th>
                                        <th class="p-2 text-right border border-gray-300 dark:border-gray-600">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payments->pluck('pays')->flatten() as $payment)
                                        <tr class="dark:hover:bg-gray-700">
                                            <td class="p-2 border border-gray-300 dark:border-gray-600 whitespace-nowrap">
                                                {{ $payment->created_at->format('M d, Y h:i a') }}
                                            </td>
                                            <td class="p-2 text-right border border-gray-300 dark:border-gray-600">
                                                {{ $payment->status1 }}
                                            </td>
                                            <td class="p-2 text-right border border-gray-300 dark:border-gray-600 whitespace-nowrap">
                                                ₱{{ number_format($payment->amount, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="p-2 text-center">No payments recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="space-y-2">
                            <div class="text-base font-bold text-right sm:text-l">
                                Total Amount Paid: ₱{{ number_format($totalPays, 2) }}
                            </div>
                            @php
                                $isNegative = $remainingBalance < 0;
                            @endphp

                            <hr style="margin: 20px 0; border-top: 2px solid #ccc;" />

                            @if ($isNegative)
                                <div style="text-align: right; font-weight: 900; font-size: 20px; color: green;">
                                    Fully Paid!
                                </div>
                                <div style="text-align: right; font-weight: 900; font-size: 18px; color: goldenrod;">
                                    Refundable Balance: ₱{{ number_format(abs($remainingBalance), 2) }}
                                </div>
                            @elseif ($remainingBalance == 0)
                                <div style="text-align: right; font-weight: 900; font-size: 20px; color: green;">
                                    Fully Paid!
                                </div>
                            @else
                                <div style="text-align: right; font-weight: 900; font-size: 20px; color: red;">
                                    Remaining Balance: ₱{{ number_format($remainingBalance, 2) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif
    @endif
</x-filament-panels::page>
