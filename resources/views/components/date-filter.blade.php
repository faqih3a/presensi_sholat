@props([
    'actionRoute',
    'tanggalMulai',
    'tanggalAkhir',
    'extraInputs' => []
])

@php
    $currentPeriod = request('period', 'day');
    $startDate = \Carbon\Carbon::parse($tanggalMulai);
    $endDate = \Carbon\Carbon::parse($tanggalAkhir);
    
    $startDate->locale('id');
    $endDate->locale('id');
    
    $dateLabel = $startDate->translatedFormat('F Y');
    $currentYear = $startDate->year;
    $currentMonth = $startDate->month;
@endphp

<div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end">
    <!-- Period Selection Tab -->
    <div class="btn-group bg-white p-1 rounded-pill border shadow-sm" role="group">
        <button type="button" onclick="changePeriod('day')" class="btn rounded-pill px-4 py-2 fw-bold {{ $currentPeriod == 'day' ? 'btn-success text-white' : 'btn-light text-success bg-transparent border-0' }}" style="font-size: 0.9rem; min-width: 85px;">Day</button>
        <button type="button" onclick="changePeriod('week')" class="btn rounded-pill px-4 py-2 fw-bold {{ $currentPeriod == 'week' ? 'btn-success text-white' : 'btn-light text-success bg-transparent border-0' }}" style="font-size: 0.9rem; min-width: 85px;">Week</button>
        <button type="button" onclick="changePeriod('month')" class="btn rounded-pill px-4 py-2 fw-bold {{ $currentPeriod == 'month' ? 'btn-success text-white' : 'btn-light text-success bg-transparent border-0' }}" style="font-size: 0.9rem; min-width: 85px;">Month</button>
    </div>

    <div class="d-flex align-items-center gap-2">
        <!-- Arrow Left -->
        <button type="button" onclick="navigate(-1)" class="btn btn-white border rounded-3 px-3.5 py-2 shadow-sm text-secondary" style="display: flex; align-items: center; justify-content: center; height: 38px;">
            <i class="bi bi-chevron-left"></i>
        </button>

        <!-- Month Dropdown Toggle -->
        <div class="dropdown d-inline-block">
            <button class="btn btn-white border rounded-3 px-4 py-2 shadow-sm fw-bold text-success dropdown-toggle no-caret" type="button" id="date-label-btn" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem; min-width: 170px; height: 38px;">
                {{ $dateLabel }}
            </button>
            <div class="dropdown-menu dropdown-menu-end p-3 border-0 shadow-lg rounded-4 mt-2" aria-labelledby="date-label-btn" style="min-width: 280px; z-index: 1050;">
                <!-- Year Navigation -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button type="button" class="btn btn-link btn-sm text-dark p-0" id="btn-prev-year"><i class="bi bi-chevron-left"></i></button>
                    <span class="fw-bold text-dark" id="year-display">{{ $currentYear }}</span>
                    <button type="button" class="btn btn-link btn-sm text-dark p-0" id="btn-next-year"><i class="bi bi-chevron-right"></i></button>
                </div>
                <!-- Months Grid -->
                <div class="row g-2 text-center" id="months-grid">
                    @php
                        $months = [
                            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 
                            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt', 
                            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                        ];
                    @endphp
                    @foreach($months as $num => $name)
                        <div class="col-4">
                            <button type="button" class="btn btn-sm w-100 py-2 rounded-3 fw-semibold month-select-btn {{ $num == $currentMonth ? 'btn-success text-white' : 'btn-light text-dark bg-transparent border-0' }}" data-month="{{ $num }}">
                                {{ $name }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Arrow Right -->
        <button type="button" onclick="navigate(1)" class="btn btn-white border rounded-3 px-3.5 py-2 shadow-sm text-secondary" style="display: flex; align-items: center; justify-content: center; height: 38px;">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>

    <!-- Hidden Form for GET submission -->
    <form id="filter-form" action="{{ $actionRoute }}" method="GET" class="no-loader d-none">
        <input type="hidden" name="period" id="filter-period" value="{{ $currentPeriod }}">
        <input type="hidden" name="tanggal_mulai" id="filter-tanggal-mulai" value="{{ $tanggalMulai }}">
        <input type="hidden" name="tanggal_akhir" id="filter-tanggal-akhir" value="{{ $tanggalAkhir }}">
        @foreach($extraInputs as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    </form>
</div>

@once
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        window.currentPeriod = "{{ $currentPeriod }}";
        window.startDateStr = "{{ $tanggalMulai }}";
        window.endDateStr = "{{ $tanggalAkhir }}";
        window.currentYear = parseInt("{{ $currentYear }}");

        window.formatDateISO = function(date) {
            let yyyy = date.getFullYear();
            let mm = String(date.getMonth() + 1).padStart(2, '0');
            let dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        window.changePeriod = function(period) {
            let date = new Date(window.startDateStr);
            if (isNaN(date.getTime())) {
                date = new Date();
            }
            
            if (period === 'day') {
                let dateStr = window.formatDateISO(date);
                document.getElementById('filter-period').value = 'day';
                document.getElementById('filter-tanggal-mulai').value = dateStr;
                document.getElementById('filter-tanggal-akhir').value = dateStr;
            } else if (period === 'week') {
                let day = date.getDay();
                let diffToMonday = date.getDate() - day + (day === 0 ? -6 : 1);
                let monday = new Date(date.setDate(diffToMonday));
                let sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);
                
                document.getElementById('filter-period').value = 'week';
                document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(monday);
                document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(sunday);
            } else if (period === 'month') {
                let firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
                let lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
                
                document.getElementById('filter-period').value = 'month';
                document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(lastDay);
            }
            document.getElementById('filter-form').submit();
        }

        window.navigate = function(direction) {
            let date = new Date(window.startDateStr);
            if (isNaN(date.getTime())) {
                date = new Date();
            }
            
            if (window.currentPeriod === 'day') {
                date.setDate(date.getDate() + direction);
                let dateStr = window.formatDateISO(date);
                document.getElementById('filter-tanggal-mulai').value = dateStr;
                document.getElementById('filter-tanggal-akhir').value = dateStr;
            } else if (window.currentPeriod === 'week') {
                date.setDate(date.getDate() + (direction * 7));
                let day = date.getDay();
                let diffToMonday = date.getDate() - day + (day === 0 ? -6 : 1);
                let monday = new Date(date.setDate(diffToMonday));
                let sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);
                
                document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(monday);
                document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(sunday);
            } else if (window.currentPeriod === 'month') {
                date.setMonth(date.getMonth() + direction);
                let firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
                let lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
                
                document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(lastDay);
            }
            document.getElementById('filter-form').submit();
        }

        const yearDisplay = document.getElementById('year-display');
        const btnPrevYear = document.getElementById('btn-prev-year');
        const btnNextYear = document.getElementById('btn-next-year');
        
        if (yearDisplay && btnPrevYear && btnNextYear) {
            btnPrevYear.addEventListener('click', (e) => {
                e.stopPropagation();
                window.currentYear--;
                yearDisplay.textContent = window.currentYear;
                updateMonthGridActiveYear();
            });
            
            btnNextYear.addEventListener('click', (e) => {
                e.stopPropagation();
                window.currentYear++;
                yearDisplay.textContent = window.currentYear;
                updateMonthGridActiveYear();
            });
        }

        const monthButtons = document.querySelectorAll('.month-select-btn');
        monthButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const monthNum = parseInt(btn.getAttribute('data-month'));
                
                if (window.currentPeriod === 'day') {
                    const firstDay = new Date(window.currentYear, monthNum - 1, 1);
                    document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                    document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(firstDay);
                } else if (window.currentPeriod === 'week') {
                    const firstDay = new Date(window.currentYear, monthNum - 1, 1);
                    const seventhDay = new Date(window.currentYear, monthNum - 1, 7);
                    document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                    document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(seventhDay);
                } else {
                    const firstDay = new Date(window.currentYear, monthNum - 1, 1);
                    const lastDay = new Date(window.currentYear, monthNum, 0);
                    document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                    document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(lastDay);
                }
                
                document.getElementById('filter-period').value = window.currentPeriod;
                document.getElementById('filter-form').submit();
            });
        });

        function updateMonthGridActiveYear() {
            const phpMonth = parseInt("{{ $currentMonth }}");
            const phpYear = parseInt("{{ $currentYear }}");
            
            monthButtons.forEach(btn => {
                const m = parseInt(btn.getAttribute('data-month'));
                if (window.currentYear === phpYear && m === phpMonth) {
                    btn.classList.remove('btn-light', 'bg-transparent', 'border-0', 'text-dark');
                    btn.classList.add('btn-success', 'text-white');
                } else {
                    btn.classList.add('btn-light', 'bg-transparent', 'border-0', 'text-dark');
                    btn.classList.remove('btn-success', 'text-white');
                }
            });
        }
    });
</script>
@endpush
@endonce
