<script>
document.addEventListener('DOMContentLoaded', function() {

    // Table view is the only view - no switching needed

    // Search functionality removed - using event filter only

    // Sorting functionality for table
    const sortableHeaders = document.querySelectorAll('.osb-sortable');
    let currentSort = { column: 'position', direction: 'asc' };

    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sortType = this.dataset.sort;
            const direction = currentSort.column === sortType && currentSort.direction === 'asc' ? 'desc' : 'asc';

            sortTable(sortType, direction);
            updateSortIcons(this, direction);

            currentSort = { column: sortType, direction };
        });
    });

    function sortTable(column, direction) {
        const tbody = document.querySelector('.osb-results-table tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            let aVal, bVal;

            switch(column) {
                case 'position':
                    aVal = parseInt(a.dataset.position);
                    bVal = parseInt(b.dataset.position);
                    break;
                case 'student':
                    aVal = a.querySelector('.osb-name').textContent.toLowerCase();
                    bVal = b.querySelector('.osb-name').textContent.toLowerCase();
                    break;
                case 'school':
                    aVal = a.querySelector('.osb-school').textContent.toLowerCase();
                    bVal = b.querySelector('.osb-school').textContent.toLowerCase();
                    break;
                case 'accuracy':
                    aVal = parseFloat(a.querySelector('.osb-accuracy-text').textContent);
                    bVal = parseFloat(b.querySelector('.osb-accuracy-text').textContent);
                    break;
                case 'time':
                    aVal = a.querySelector('.osb-time').textContent;
                    bVal = b.querySelector('.osb-time').textContent;
                    break;
                default:
                    return 0;
            }

            if (aVal < bVal) return direction === 'asc' ? -1 : 1;
            if (aVal > bVal) return direction === 'asc' ? 1 : -1;
            return 0;
        });

        // Animate the sorting
        tbody.style.opacity = '0.5';

        setTimeout(() => {
            rows.forEach(row => tbody.appendChild(row));
            tbody.style.opacity = '1';
        }, 150);
    }

    function updateSortIcons(activeHeader, direction) {
        // Reset all icons
        sortableHeaders.forEach(header => {
            const icon = header.querySelector('.osb-sort-icon');
            icon.textContent = '↕️';
        });

        // Update active icon
        const activeIcon = activeHeader.querySelector('.osb-sort-icon');
        activeIcon.textContent = direction === 'asc' ? '↑' : '↓';
    }

    function updateNoResultsMessage(searchTerm) {
        const existingMessage = document.querySelector('.osb-no-search-results');

        if (searchTerm) {
            const visibleRows = document.querySelectorAll('.osb-table-row[style*="display: table-row"], .osb-table-row:not([style*="display: none"])').length;

            if (visibleRows === 0) {
                if (!existingMessage) {
                    const message = document.createElement('div');
                    message.className = 'osb-no-search-results';
                    message.innerHTML = `
                        <div class="osb-empty-state">
                            <div class="osb-empty-icon">🔍</div>
                            <h3>No Results Found</h3>
                            <p>No students or schools match your search for "<strong>${searchTerm}</strong>"</p>
                            <button class="osb-btn osb-btn-refresh" onclick="document.getElementById('results-search').value=''; document.getElementById('results-search').dispatchEvent(new Event('input'));">Clear Search</button>
                        </div>
                    `;

                    const detailedResults = document.querySelector('.osb-detailed-results');
                    detailedResults.appendChild(message);
                }
            }
        } else {
            if (existingMessage) {
                existingMessage.remove();
            }
        }
    }

    // Export functionality
    window.exportResults = function() {
        const eventTitle = document.querySelector('.osb-event-title').textContent;
        const results = [];

        // Collect data from table
        const rows = document.querySelectorAll('.osb-table-row');
        rows.forEach(row => {
            const position = row.querySelector('.osb-position-num').textContent;
            const student = row.querySelector('.osb-name').textContent;
            const school = row.querySelector('.osb-school').textContent;
            const accuracy = row.querySelector('.osb-accuracy-text').textContent;
            const time = row.querySelector('.osb-time').textContent;

            results.push({ position, student, school, accuracy, time });
        });

        // Create CSV content
        const csvContent = [
            ['Position', 'Student Name', 'School', 'Accuracy', 'Time'],
            ...results.map(r => [r.position, r.student, r.school, r.accuracy, r.time])
        ].map(row => row.join(',')).join('\n');

        // Download file
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${eventTitle.replace(/[^a-zA-Z0-9]/g, '_')}_Results.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    };

    // Quick event selection
    window.selectEvent = function(eventYear) {
        const eventFilter = document.getElementById('event-filter');
        if (eventFilter) {
            eventFilter.value = eventYear;
            eventFilter.form.submit();
        }
    };

    // No progress rings in table-only view

    // Animate accuracy bars
    function animateAccuracyBars() {
        const bars = document.querySelectorAll('.osb-bar-fill');

        bars.forEach(bar => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const targetWidth = bar.style.width;
                        bar.style.width = '0%';

                        setTimeout(() => {
                            bar.style.width = targetWidth;
                        }, 100);

                        observer.unobserve(entry.target);
                    }
                });
            });

            observer.observe(bar);
        });
    }

    // Initialize animations
    animateAccuracyBars();

    // Table-only view - no podium or cards

    // Enhanced table interactions for better UX
    const tableRows = document.querySelectorAll('.osb-table-row');
    tableRows.forEach((row, index) => {
        // Add subtle entrance animation
        row.style.animationDelay = `${index * 0.1}s`;
        row.classList.add('osb-fade-in');

        // Enhanced hover effects for top 3
        const position = parseInt(row.dataset.position);
        if (position <= 3) {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02) translateY(-2px)';
            });

            row.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        }
    });

    // Add loading skeleton for dynamic content
    function showLoadingSkeleton() {
        const detailedResults = document.querySelector('.osb-detailed-results');
        if (detailedResults) {
            detailedResults.innerHTML = `
                <div class="osb-loading-skeleton">
                    <div class="osb-skeleton-header"></div>
                    <div class="osb-skeleton-cards">
                        ${Array(6).fill().map(() => `
                            <div class="osb-skeleton-card">
                                <div class="osb-skeleton-line"></div>
                                <div class="osb-skeleton-line short"></div>
                                <div class="osb-skeleton-circle"></div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
    }

    // Add smooth scrolling to sections
    function smoothScrollToResults() {
        const detailedResults = document.querySelector('.osb-detailed-results');
        if (detailedResults) {
            detailedResults.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    // Performance metrics calculation
    function calculatePerformanceMetrics() {
        const results = document.querySelectorAll('.osb-result-card');
        const metrics = {
            totalParticipants: results.length,
            averageAccuracy: 0,
            topPerformers: 0,
            schoolsRepresented: new Set()
        };

        results.forEach(card => {
            const accuracy = parseFloat(card.querySelector('.osb-ring-text').textContent);
            const school = card.querySelector('.osb-school-name').textContent;

            metrics.averageAccuracy += accuracy;
            metrics.schoolsRepresented.add(school);

            if (accuracy >= 90) {
                metrics.topPerformers++;
            }
        });

        metrics.averageAccuracy = metrics.totalParticipants > 0 ?
            (metrics.averageAccuracy / metrics.totalParticipants).toFixed(1) : 0;
        metrics.schoolsRepresented = metrics.schoolsRepresented.size;

        return metrics;
    }

    // Initialize tooltips for better UX
    function initializeTooltips() {
        const badges = document.querySelectorAll('.osb-badge, .osb-mini-badge');

        badges.forEach(badge => {
            badge.addEventListener('mouseenter', function(e) {
                const tooltip = document.createElement('div');
                tooltip.className = 'osb-tooltip';
                tooltip.textContent = getTooltipText(this);

                document.body.appendChild(tooltip);

                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';

                setTimeout(() => tooltip.classList.add('visible'), 10);
            });

            badge.addEventListener('mouseleave', function() {
                const tooltip = document.querySelector('.osb-tooltip');
                if (tooltip) {
                    tooltip.remove();
                }
            });
        });
    }

    function getTooltipText(element) {
        if (element.classList.contains('osb-badge-excellence')) {
            return 'Achieved 90%+ accuracy - Outstanding performance!';
        } else if (element.classList.contains('osb-badge-streak')) {
            return 'Got 10+ words correct in a row - Amazing streak!';
        } else if (element.textContent === '⭐') {
            return 'Top performer with exceptional accuracy';
        }
        return '';
    }

    // Initialize all enhancements
    initializeTooltips();

    console.log('Competition Results enhanced UI loaded successfully!');
});

// Add tooltip styles dynamically
const tooltipStyles = `
    .osb-tooltip {
        position: absolute;
        background: rgba(0,0,0,0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        white-space: nowrap;
        opacity: 0;
        transform: translateY(5px);
        transition: all 0.3s ease;
        z-index: 10000;
        pointer-events: none;
    }

    .osb-tooltip.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .osb-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: rgba(0,0,0,0.9);
    }

    .osb-loading-skeleton {
        padding: 2rem;
    }

    .osb-skeleton-header {
        height: 40px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        border-radius: 8px;
        margin-bottom: 2rem;
        animation: skeleton-loading 1.5s infinite;
    }

    .osb-skeleton-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }

    .osb-skeleton-card {
        padding: 1rem;
        background: white;
        border-radius: 15px;
        border: 2px solid #f0f0f0;
    }

    .osb-skeleton-line {
        height: 20px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        border-radius: 4px;
        margin-bottom: 1rem;
        animation: skeleton-loading 1.5s infinite;
    }

    .osb-skeleton-line.short {
        width: 60%;
    }

    .osb-skeleton-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        animation: skeleton-loading 1.5s infinite;
        margin: 0 auto;
    }

    @keyframes skeleton-loading {
        0% { background-position: -200px 0; }
        100% { background-position: calc(200px + 100%) 0; }
    }
`;

// Inject tooltip styles
const style = document.createElement('style');
style.textContent = tooltipStyles;
document.head.appendChild(style);
</script>