<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
            color: #333;
        }
        .receipt-container {
            background: #ffffff; /* Set background color for the receipt */
            padding: 10px; /* Adjust padding for print */
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 4in; /* Set width to 4 inches */
            height: 2in; /* Set height to 2 inches */
            margin: auto; /* Center the receipt */
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 10px; /* Adjust margin for print */
        }
        .receipt-header h1 {
            font-size: 1.5em; /* Adjust font size for print */
            color: #2c3e50;
        }
        .receipt-header p {
            margin: 0;
            font-size: 0.8em; /* Adjust font size for print */
            color: #7f8c8d;
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px; /* Adjust margin for print */
        }
        .receipt-table th, .receipt-table td {
            text-align: left;
            padding: 5px; /* Adjust padding for print */
            border: 1px solid #e1e4e8;
        }
        .receipt-table th {
            background: #ecf0f1;
            font-weight: bold;
        }
        .amount-cell {
            font-weight: bold;
            color: #27ae60;
        }
        .footer-note {
            font-size: 0.75em; /* Adjust font size for print */
            color: #7f8c8d;
            text-align: center;
        }

        /* Print styles */
        @media print {
            body {
                margin: 0; /* Remove margin for print */
                padding: 0; /* Remove padding for print */
                overflow: hidden; /* Prevent overflow */
            }
            .receipt-container {
                box-shadow: none; /* Remove shadow for print */
                padding: 10px; /* Adjust padding for print */
            }
            .no-print {
                display: none; /* Hide elements with the class no-print */
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Payment Receipt</h1>
            <p>Thank you for your payment!</p>
        </div>
        <table class="receipt-table">
            <tr>
                <th>Receipt ID</th>
                <td>{{ $id }}</td>
            </tr>
            <tr>
                <th>Amount Paid</th>
                <td class="amount-cell">{{ $amount_formatted }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $status }}</td>
            </tr>
            <tr>
                <th>Date Paid</th>
                <td>{{ $date }}</td>
            </tr>
            <tr>
                <th>Enrollment ID</th>
                <td>{{ $enrollment['id'] }}</td>
            </tr>
            <tr>
                <th>Student Name</th>
                <td>{{ $enrollment['lastname'] . ', ' . $enrollment['firstname'] . ' ' . $enrollment['middlename'] }}</td>
            </tr>
        </table>
        <div class="footer-note">
            <p>This is an automated receipt. For any inquiries, please contact our support.</p>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('printReceipt', (url) => {
                const pdfWindow = window.open(url, '_blank');
                pdfWindow.onload = function () {
                    pdfWindow.print();
                };
            });
        });
    </script>

</body>
</html>
