<style>
/* Enhanced Competition Results Styles */
.osb-competition-results {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: #f8f9fa;
    border-radius: 20px;
}

/* Header Section */
.osb-results-header {
    background: linear-gradient(135deg, #0052cc 0%, #003d99 50%, #004080 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 82, 204, 0.3);
}

.osb-results-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><radialGradient id="grad1" cx="50%" cy="50%" r="50%"><stop offset="0%" style="stop-color:rgba(255,255,255,0.15);stop-opacity:1" /><stop offset="100%" style="stop-color:rgba(255,255,255,0);stop-opacity:0" /></radialGradient></defs><circle cx="30" cy="40" r="15" fill="url(%23grad1)"/><circle cx="170" cy="60" r="20" fill="rgba(255,255,255,0.08)"/><circle cx="80" cy="180" r="12" fill="rgba(255,255,255,0.1)"/><circle cx="150" cy="150" r="8" fill="rgba(255,255,255,0.12)"/><polygon points="20,20 40,10 50,35 25,40" fill="rgba(255,255,255,0.05)"/><polygon points="160,30 180,20 185,45 165,50" fill="rgba(255,255,255,0.07)"/></svg>');
    pointer-events: none;
}

.osb-results-header::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
    pointer-events: none;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(5deg); }
}

.osb-header-content {
    position: relative;
    z-index: 2;
    text-align: center;
    margin-bottom: 2rem;
}

.osb-results-title {
    font-size: 3rem;
    font-weight: 800;
    margin: 0 0 0.5rem 0;
    color: white;
    text-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.osb-results-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin: 0;
    font-weight: 400;
}

.osb-controls-section {
    position: relative;
    z-index: 2;
}

.osb-filters-panel {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 2rem;
    background: rgba(255,255,255,0.15);
    padding: 1.5rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.osb-filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 250px;
}

.osb-filter-form {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.osb-filter-group label {
    font-weight: 600;
    font-size: 0.9rem;
    opacity: 0.9;
}

.osb-filter-group select {
    padding: 12px 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 10px;
    font-size: 1rem;
    background: rgba(255,255,255,0.9);
    color: #333;
    transition: all 0.3s ease;
    cursor: pointer;
}

.osb-filter-group select:focus {
    outline: none;
    border-color: rgba(255,255,255,0.8);
    background: white;
    box-shadow: 0 0 0 3px rgba(255,255,255,0.2);
}

.osb-filter-group select:hover {
    border-color: rgba(255,255,255,0.6);
}

.osb-action-buttons {
    display: flex;
    gap: 0.75rem;
}

.osb-btn {
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.osb-btn-export {
    background: #28a745;
    color: white;
}

.osb-btn-print {
    background: #6c757d;
    color: white;
}

.osb-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Event Card */
.osb-event-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.osb-event-header {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 2rem;
    align-items: center;
}

.osb-event-title {
    font-size: 2rem;
    color: #333;
    margin: 0 0 1rem 0;
    font-weight: 700;
}

.osb-event-meta {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.osb-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.95rem;
}

.osb-icon {
    font-size: 1.2rem;
}

.osb-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-status-upcoming { background: #fff3cd; color: #856404; }
.osb-status-live { background: #f8d7da; color: #721c24; }
.osb-status-completed { background: #d4edda; color: #155724; }

.osb-competition-stats {
    display: flex;
    gap: 2rem;
}

.osb-stat-item {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 15px;
    min-width: 100px;
}

.osb-stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 800;
    color: #0052cc;
    line-height: 1;
}

.osb-stat-label {
    font-size: 0.85rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.5rem;
}

/* Section Title */
.osb-section-title {
    font-size: 1.8rem;
    color: #333;
    margin: 0 0 2rem 0;
    text-align: center;
    font-weight: 700;
}

/* Detailed Results */
.osb-detailed-results {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}

.osb-results-header-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 2rem;
}

/* Table View */
.osb-results-table-view {
    display: block;
}

.osb-table-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.osb-results-table {
    width: 100%;
    border-collapse: collapse;
}

.osb-results-table thead {
    background: linear-gradient(135deg, #0052cc 0%, #003d99 50%, #004080 100%);
    color: white;
}

.osb-results-table th {
    padding: 1.5rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.osb-sortable:hover {
    background: rgba(255,255,255,0.1);
}

.osb-sort-icon {
    margin-left: 0.5rem;
    opacity: 0.7;
}

.osb-results-table tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.osb-results-table tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
}

/* Top 3 Color Coding */
.osb-table-row.osb-rank-1 {
    background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(255, 237, 78, 0.1) 100%);
    border-left: 4px solid #ffd700;
}

.osb-table-row.osb-rank-1:hover {
    background: linear-gradient(135deg, rgba(255, 215, 0, 0.25) 0%, rgba(255, 237, 78, 0.15) 100%);
}

.osb-table-row.osb-rank-2 {
    background: linear-gradient(135deg, rgba(192, 192, 192, 0.15) 0%, rgba(232, 232, 232, 0.1) 100%);
    border-left: 4px solid #c0c0c0;
}

.osb-table-row.osb-rank-2:hover {
    background: linear-gradient(135deg, rgba(192, 192, 192, 0.25) 0%, rgba(232, 232, 232, 0.15) 100%);
}

.osb-table-row.osb-rank-3 {
    background: linear-gradient(135deg, rgba(205, 127, 50, 0.15) 0%, rgba(222, 184, 135, 0.1) 100%);
    border-left: 4px solid #cd7f32;
}

.osb-table-row.osb-rank-3:hover {
    background: linear-gradient(135deg, rgba(205, 127, 50, 0.25) 0%, rgba(222, 184, 135, 0.15) 100%);
}

.osb-results-table td {
    padding: 1rem;
    vertical-align: middle;
}

.osb-position-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
}

.osb-medal-small {
    font-size: 1rem;
}

.osb-student-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.osb-mini-badge {
    font-size: 0.8rem;
}

.osb-accuracy-bar {
    position: relative;
    background: #e9ecef;
    border-radius: 10px;
    height: 20px;
    overflow: hidden;
}

.osb-bar-fill {
    background: linear-gradient(90deg, #28a745, #20c997);
    height: 100%;
    border-radius: 10px;
    transition: width 0.5s ease;
}

.osb-accuracy-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.8rem;
    font-weight: 600;
    color: #333;
}

.osb-performance-indicators {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.osb-indicator {
    font-size: 0.8rem;
    color: #666;
}

/* Empty States */
.osb-empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.05);
}

.osb-empty-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    opacity: 0.7;
}

.osb-empty-state h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.5rem;
    font-weight: 700;
}

.osb-empty-state p {
    color: #666;
    font-size: 1.1rem;
    line-height: 1.6;
    max-width: 500px;
    margin: 0 auto 2rem auto;
}

.osb-btn-refresh {
    background: #0052cc;
    color: white;
}

.osb-progress-indicator {
    margin: 2rem 0;
}

.osb-progress-dots {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.osb-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #e9ecef;
    transition: background 0.3s ease;
}

.osb-dot.active {
    background: #0052cc;
}

.osb-progress-text {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

.osb-quick-select {
    margin-top: 2rem;
}

.osb-quick-select h4 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.osb-event-quick-links {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.osb-quick-link {
    padding: 0.75rem 1.5rem;
    background: #0052cc;
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.osb-quick-link:hover {
    background: #003d99;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .osb-filters-panel {
        flex-direction: column;
        gap: 1.5rem;
        align-items: stretch;
    }

    .osb-filter-group {
        min-width: auto;
    }

    .osb-action-buttons {
        justify-content: center;
    }

    .osb-event-header {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .osb-competition-stats {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .osb-competition-results {
        padding: 1rem;
    }

    .osb-results-header {
        padding: 2rem 1rem;
    }

    .osb-results-title {
        font-size: 2rem;
    }

    .osb-podium {
        flex-direction: column;
        align-items: center;
    }

    .osb-podium-stand {
        width: 200px;
        height: 60px !important;
    }

    .osb-results-grid {
        grid-template-columns: 1fr;
    }

    .osb-performance-metrics {
        grid-template-columns: 1fr;
    }

    .osb-results-header-controls {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .osb-table-container {
        overflow-x: auto;
    }

    .osb-results-table {
        min-width: 600px;
    }
}

@media (max-width: 480px) {
    .osb-event-card,
    .osb-podium-section,
    .osb-detailed-results {
        padding: 1rem;
    }

    .osb-filters-panel {
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .osb-event-meta {
        flex-direction: column;
        gap: 1rem;
    }

    .osb-competition-stats {
        flex-direction: column;
        gap: 1rem;
    }

    .osb-results-header {
        padding: 2rem 1rem;
    }

    .osb-results-title {
        font-size: 2rem;
    }

    .osb-results-subtitle {
        font-size: 1rem;
    }

    .osb-filter-group {
        min-width: auto;
    }

    .osb-filter-group select {
        padding: 10px 12px;
        font-size: 0.9rem;
    }

    .osb-action-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }

    .osb-btn {
        padding: 10px 16px;
        font-size: 0.85rem;
    }

    .osb-results-table {
        min-width: 500px;
        font-size: 0.85rem;
    }

    .osb-results-table th,
    .osb-results-table td {
        padding: 0.75rem 0.5rem;
    }

    .osb-position-badge {
        flex-direction: column;
        gap: 0.25rem;
        text-align: center;
    }

    .osb-medal-small {
        font-size: 0.9rem;
    }

    .osb-accuracy-bar {
        height: 16px;
    }

    .osb-accuracy-text {
        font-size: 0.7rem;
    }

    .osb-performance-indicators {
        gap: 0.1rem;
    }

    .osb-indicator {
        font-size: 0.7rem;
    }

    .osb-stat-item {
        padding: 0.75rem;
        min-width: 80px;
    }

    .osb-stat-number {
        font-size: 1.5rem;
    }

    .osb-stat-label {
        font-size: 0.75rem;
    }
}

/* Extra Small Mobile Devices */
@media (max-width: 320px) {
    .osb-competition-results {
        padding: 1rem;
    }

    .osb-results-header {
        padding: 1.5rem 0.5rem;
    }

    .osb-results-title {
        font-size: 1.8rem;
    }

    .osb-event-card,
    .osb-detailed-results {
        padding: 0.75rem;
    }

    .osb-filters-panel {
        padding: 0.75rem;
    }

    .osb-filter-group select {
        padding: 8px 10px;
        font-size: 0.85rem;
    }

    .osb-btn {
        padding: 8px 12px;
        font-size: 0.8rem;
    }

    .osb-results-table {
        min-width: 450px;
        font-size: 0.8rem;
    }

    .osb-results-table th,
    .osb-results-table td {
        padding: 0.5rem 0.25rem;
    }

    .osb-stat-item {
        padding: 0.5rem;
        min-width: 70px;
    }

    .osb-stat-number {
        font-size: 1.3rem;
    }

    .osb-stat-label {
        font-size: 0.7rem;
    }
}

/* Print Styles */
@media print {
    .osb-competition-results {
        background: white !important;
        box-shadow: none !important;
    }

    .osb-controls-section,
    .osb-action-buttons,
    .osb-view-controls {
        display: none !important;
    }

    .osb-results-cards-view {
        display: none !important;
    }

    .osb-results-table-view {
        display: block !important;
    }

    .osb-results-table tbody tr:hover {
        background: white !important;
        transform: none !important;
    }
}

/* Animation for loading states */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.osb-fade-in {
    animation: fadeIn 0.5s ease forwards;
}
</style>