<style>
    .content td {
        border: 1px solid black;
    }

    .header td {
        font-size: 1.15em;
    }

    img {
        width: 40px;
        height: 40px;
    }
</style>
<body>
<table class="header">
    <tbody>
    <tr>
        <td rowspan="3" style="width: 100px;">
            <img src="{{ asset("aroroy_masbate_seal_logo.png") }}" alt="Logo" style="width: 70px; height: 70px">
        </td>
        <td style="font-weight: bold; font-size: 1.5em">General Payroll</td>
    </tr>
    <tr>
        <td>Office: {{ $output["office"] }}</td>
    </tr>
    <tr>
        <td>Period Cover: {{ $output["period_cover"] }}</td>
    </tr>
    </tbody>
</table>
<br>
<table class="content" cellpadding="0" cellspacing="0">
    <tr style="text-align: center;">
        <td style="width: 10px">#</td>
        <td style="width: 50px">Emp. ID</td>
        <td>Name</td>
        <td style="width: 10px">Position / Designation</td>
        <td style="width: 45px">Basic Salary</td>
        @foreach($output["inclusion_ids"] as $inclusion)
            <td style="width: 45px">{{ $inclusion["name"] }}</td>
        @endforeach
        <td style="width: 45px">Gross Amount Earned</td>
        @foreach($output["deduction_ids"] as $deduction)
            <td style="width: 45px">{{ $deduction["name"] }}</td>
        @endforeach
        <td style="width: 45px">Total Deductions</td>
        <td style="width: 45px">Net Take Home Pay</td>
        <td style="width: 51px">Signature of Employee</td>
    </tr>
    @foreach($output["data"] as $key => $data)
        <tr class="item_row" style="text-align: center; border-width: 2px; line-height: 20px;">
            <td style="width: 15px">{{ $key + 1 }}</td>
            <td style="width: 35px">{{ $data["emp_id"] }}</td>
            <td style="text-align: left">{{ $data["full_name"] }}</td>
            <td>{{ $data["position"] }}</td>
            <td style="width: 45px; padding: 0 5px">{{ $data["basic_salary"] }}</td>
            @foreach($output["inclusion_ids"] as $inclusion)
                <td style="width: 45px; padding: 0 5px">
                    @if(in_array($inclusion["id"], array_column($data["inclusions"], 'id')))
                        {{ $data["inclusions"][array_search($inclusion["id"], array_column($data["inclusions"], 'id'))]["amount"] }}
                    @else
                        0.00
                    @endif
                </td>
            @endforeach
            <td style="width: 45px">{{ $data["total_receivables"] }}</td>
            @foreach($output["deduction_ids"] as $deduction)
                <td style="width: 45px; padding: 0 5px">
                    @if(in_array($deduction["id"], array_column($data["deductions"], 'id')))
                        {{ $data["deductions"][array_search($deduction["id"], array_column($data["deductions"], 'id'))]["amount"] }}
                    @else
                        0.00
                    @endif
                </td>
            @endforeach
            <td style="width: 45px; padding: 0 5px">{{ $data["total_deductions"] }}</td>
            <td style="width: 45px; padding: 0 5px">{{ $data["net_home_pay"] }}</td>
            <td style="width: 51px; text-align: left">{{ $key + 1 . ". _________" }}</td>
        </tr>
    @endforeach
    <tr class="item_row" style="text-align: center; border-width: 2px; line-height: 20px;">
        <td style="width: 15px"></td>
        <td style="width: 35px"></td>
        <td></td>
        <td><b>Total: </b></td>
        <td style="width: 45px; padding: 0 5px">{{ $output["overall_basic_salary"] }}</td>
        @foreach($output["inclusion_ids"] as $inclusion)
            <td style="width: 45px; padding: 0 5px"></td>
        @endforeach
        <td style="width: 45px; padding: 0 5px">{{ $output["overall_receivables"] }}</td>
        @foreach($output["deduction_ids"] as $deduction)
            <td style="width: 45px; padding: 0 5px"></td>
        @endforeach
        <td style="width: 45px; padding: 0 5px">{{ $output["overall_deductions"] }}</td>
        <td style="width: 45px; padding: 0 5px">{{ $output["overall_home_pay"] }}</td>
        <td style="width: 51px"></td>
    </tr>
</table>
<br>
<br>
<table style="width: 100%; font-size: 1.1em">
    <tr>
        <td style="width: 50%">Certified: Services has been duly rendered:</td>
        <td style="width: 50%">Approved for Payment:</td>
    </tr>
    <tr style="height: 15px">
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>{{ $output["signatory"]['supervisor_text'] ?? "" }}</td>
        <td>{{ $output["signatory"]['mayor_text'] ?? "" }}</td>
    </tr>
    <tr style="font-weight: bold">
        <td>Department Head / Supervisor</td>
        <td>Municipal Mayor</td>
    </tr>
    <tr style="height: 15px">
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>Certified: Funds Available</td>
        <td>Certified: Each Employee whose name appears herein has been paid in the amount indicated opposite his/her name.</td>
    </tr>
    <tr style="height: 15px">
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>{{ $output["signatory"]['treasurer_text'] ?? "" }}</td>
        <td>{{ $output["signatory"]['treasurer_text'] ?? "" }}</td>
    </tr>
    <tr style="font-weight: bold">
        <td>Municipal Treasurer</td>
        <td>Municipal Treasurer</td>
    </tr>
</table>
</body>
