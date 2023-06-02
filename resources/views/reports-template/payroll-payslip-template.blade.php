<style>
    .container {
        width: 100%;
    }

    .logo {
        width: 20px;
        height: 20px;
        margin-left: 50px;
    }

    .header {
        font-size: 1em;
    }

    .border {
        border: 1px solid black;
    }

    .center {
        text-align: center;
    }

    @media print {
        .new-page {
            page-break-before: always;
        }
    }
</style>
<body>
@foreach($output["data"] as $data)
    <div class="new-page"></div>
    <table class="container" cellspacing="0" cellpadding="3">
        <tr>
            <td colspan="4" class="header border">
                <table style="width: 100%;">
                    <tr>
                        <td rowspan="3" style="width: 30%">
                            <img src="{{ asset("aroroy_masbate_seal_logo.png") }}" class="logo" alt="Logo" style="width: 60px; height: 60px">
                        </td>
                        <td class="center" style="width: 70%;">Local Government Unit of Aroroy</td>
                    </tr>
                    <tr>
                        <td class="center">Payslip</td>
                    </tr>
                    <tr>
                        <td class="center">{{ $output['office'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="border">Name:</td>
            <td class="border" colspan="3">{{ $data["full_name"] }}</td>
        </tr>
        <tr>
            <td class="border">Employee No:</td>
            <td class="border" colspan="3">{{ $data["emp_id"] }}</td>
        </tr>
        <tr>
            <td class="border">Position:</td>
            <td class="border" colspan="3">{{ $data["position"] }}</td>
        </tr>
        <tr>
            <td class="border">Period Covered:</td>
            <td class="border" colspan="3">{{ $data["start"] }} - {{ $data["end"] }}</td>
        </tr>
        <tr>
            <td class="border" colspan="4">Receivables:</td>
        </tr>
        <tr>
            <td class="border">Basic Salary:</td>
            <td class="border" colspan="3">{{ $data["basic_salary"] }}</td>
        </tr>
        @foreach($data["inclusions"] as $inclusion)
            <tr>
                <td class="border">{{ $inclusion["name"] }}:</td>
                <td class="border" colspan="3">{{ $inclusion["amount"] }} </td>
            </tr>
        @endforeach
        <tr>
            <td class="border">Gross Amount Earned:</td>
            <td class="border" colspan="3">{{ $data["total_receivables"] }} </td>
        </tr>
        <tr>
            <td class="border" colspan="4">Less: Deductions:</td>
        </tr>
        @foreach($data["deductions"] as $deductions)
            <tr>
                <td class="border">{{ $deductions["name"] }}:</td>
                <td class="border" colspan="3">{{ $deductions["amount"] }} </td>
            </tr>
        @endforeach
        <tr>
            <td class="border">Total Deductions:</td>
            <td class="border" colspan="3">{{ $data["total_deductions"] }} </td>
        </tr>
        <tr>
            <td class="border">Net Take Home Pay:</td>
            <td class="border" colspan="3">{{ $data["net_home_pay"] }} </td>
        </tr>
        <tr>
            <td class="border" style="width: 40%">Sick Leave:</td>
            <td class="border center" style="width: 10%">{{ $data["leave_credit_sick"] }}</td>
            <td class="border" style="width: 40%">Vacation Leave:</td>
            <td class="border center" style="width: 10%">{{ $data["leave_credit_vacation"] }}</td>
        </tr>
        <tr>
            <td colspan="4" class="border" style="height: 30px; padding: 5px 15px">
                Certified Correct: <br><br>
                {{ $output["signatory"]['accountant_text'] ?? "" }} <br>
                <b>Municipal Accountant</b>
            </td>
        </tr>
    </table>
@endforeach
</body>
