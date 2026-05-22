<?php if (isset($_SESSION['user_id'])): ?>
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">All Upcoming Events</h2>
        <p class="text-secondary small mb-0">Discover and register for exciting events.</p>
    </div>
    
    <div style="max-width: 280px; width: 100%;">
        <div class="input-group bg-white rounded-3 border shadow-sm">
            <input type="text" class="form-control border-0 bg-transparent py-2 ps-3 small" placeholder="Search events..." id="searchEvents">
            <span class="input-group-text bg-transparent border-0 pe-3 text-secondary"><i class="bi bi-search"></i></span>
        </div>
    </div>
</div>
<?php endif; ?>