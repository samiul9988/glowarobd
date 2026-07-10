<div class="row gutters-5">
    <div class="col-sm-6 col-md-4 col-lg-2 mb-2 mb-lg-0">
        <div class="bg-soft-primary rounded-lg p-3 h-100">
            <div class="text-muted fs-12">Staff</div>
            <div class="h4 fw-700 mb-0">{{ $summary['staff_count'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-lg-2 mb-2 mb-lg-0">
        <div class="bg-soft-info rounded-lg p-3 h-100">
            <div class="text-muted fs-12">Total Overtime Amount (+)</div>
            <div class="h4 fw-700 mb-0">{{ single_price($summary['overtime_total'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-lg-2 mb-2 mb-lg-0">
        <div class="bg-soft-warning rounded-lg p-3 h-100">
            <div class="text-muted fs-12">Total Bonus Amount (+)</div>
            <div class="h4 fw-700 mb-0">{{ single_price($summary['bonus_total'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-lg-2 mb-2 mb-lg-0">
        <div class="bg-soft-danger rounded-lg p-3 h-100">
            <div class="text-muted fs-12">Total Late Fee Amount (-)</div>
            <div class="h4 fw-700 mb-0">{{ single_price($summary['late_fee_total'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-lg-2 mb-2 mb-lg-0">
        <div class="bg-soft-dark rounded-lg p-3 h-100">
            <div class="text-muted fs-12">Total Leave Amount (-)</div>
            <div class="h4 fw-700 mb-0">{{ single_price($summary['leave_amount_total'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-lg-2 mb-2 mb-lg-0">
        <div class="bg-soft-success rounded-lg p-3 h-100">
            <div class="text-muted fs-12">Net Payable Amount</div>
            <div class="h4 fw-700 mb-0">{{ single_price($summary['net_salary_total'] ?? 0) }}</div>
        </div>
    </div>
</div>
