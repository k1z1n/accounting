// Мобильная оптимизация для AG-Grid таблиц
class MobileOptimizer {
    constructor() {
        this.isMobile = window.innerWidth < 768;
        this.isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
        this.grids = new Map();
        this.init();
    }

    init() {
        this.setupGlobalMobileSettings();
        this.setupTouchGestures();
        this.setupResizeHandler();
        this.setupViewportMeta();
        this.observeGrids();
    }

    setupGlobalMobileSettings() {
        // Глобальные настройки для мобильных устройств
        if (this.isMobile) {
            document.documentElement.style.setProperty('--ag-grid-mobile-font-size', '12px');
            document.documentElement.style.setProperty('--ag-grid-mobile-row-height', '40px');
            document.documentElement.style.setProperty('--ag-grid-mobile-header-height', '35px');
        }
    }

    setupViewportMeta() {
        // Настройка viewport для правильной работы touch-событий
        let viewport = document.querySelector('meta[name="viewport"]');
        if (!viewport) {
            viewport = document.createElement('meta');
            viewport.name = 'viewport';
            document.head.appendChild(viewport);
        }

        // Отключаем зум при двойном тапе для AG-Grid
        viewport.content = 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no';
    }

    setupTouchGestures() {
        // Добавляем поддержку жестов прокрутки пальцем
        document.addEventListener('DOMContentLoaded', () => {
            const gridContainers = document.querySelectorAll('.ag-theme-alpine.ag-theme-dark');

            gridContainers.forEach(container => {
                this.addTouchSupport(container);
            });
        });
    }

    addTouchSupport(container) {
        let isScrolling = false;
        let startX, startY, scrollLeft, scrollTop;

        // Поддержка горизонтальной прокрутки пальцем
        container.addEventListener('touchstart', (e) => {
            const touch = e.touches[0];
            startX = touch.pageX;
            startY = touch.pageY;
            scrollLeft = container.scrollLeft;
            scrollTop = container.scrollTop;
            isScrolling = false;
        }, { passive: true });

        container.addEventListener('touchmove', (e) => {
            if (!e.touches.length) return;

            const touch = e.touches[0];
            const deltaX = touch.pageX - startX;
            const deltaY = touch.pageY - startY;

            // Определяем направление прокрутки
            if (Math.abs(deltaX) > Math.abs(deltaY)) {
                // Горизонтальная прокрутка
                isScrolling = true;
                container.scrollLeft = scrollLeft - deltaX;
                e.preventDefault();
            }
        }, { passive: false });

        // Добавляем инерцию к прокрутке
        this.addScrollInertia(container);
    }

    addScrollInertia(container) {
        let momentum = 0;
        let lastScrollTime = 0;
        let animationId = null;

        container.addEventListener('scroll', () => {
            const now = Date.now();
            const deltaTime = now - lastScrollTime;

            if (deltaTime > 0) {
                momentum = (container.scrollLeft - (this.lastScrollLeft || 0)) / deltaTime;
                this.lastScrollLeft = container.scrollLeft;
                lastScrollTime = now;
            }
        });

        container.addEventListener('touchend', () => {
            if (Math.abs(momentum) > 0.1) {
                this.startInertialScroll(container, momentum);
            }
        });
    }

    startInertialScroll(container, initialMomentum) {
        let momentum = initialMomentum;
        const friction = 0.95;
        const minMomentum = 0.01;

        const animate = () => {
            momentum *= friction;

            if (Math.abs(momentum) > minMomentum) {
                container.scrollLeft += momentum * 16; // 16ms frame time
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    setupResizeHandler() {
        let resizeTimeout;

        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.isMobile = window.innerWidth < 768;
                this.isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
                this.optimizeAllGrids();
            }, 250);
        });
    }

    observeGrids() {
        // Наблюдаем за добавлением новых гридов
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        const grids = node.querySelectorAll ?
                                    node.querySelectorAll('.ag-theme-alpine.ag-theme-dark') :
        (node.classList && node.classList.contains('ag-theme-alpine') && node.classList.contains('ag-theme-dark') ? [node] : []);

                        grids.forEach(grid => this.optimizeGrid(grid));
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    registerGrid(name, gridApi) {
        this.grids.set(name, gridApi);
        this.optimizeGridApi(gridApi);
    }

    optimizeGrid(gridContainer) {
        if (!gridContainer) return;

        // Оптимизация контейнера
        if (this.isMobile) {
            gridContainer.style.fontSize = '12px';
            gridContainer.style.minHeight = '300px';

            // Добавляем класс для мобильных стилей
            gridContainer.classList.add('ag-grid-mobile');
        } else if (this.isTablet) {
            gridContainer.classList.add('ag-grid-tablet');
        }

        this.addTouchSupport(gridContainer);
    }

    optimizeGridApi(gridApi) {
        if (!gridApi) return;

        const isMobile = this.isMobile;
        const isTablet = this.isTablet;

        if (isMobile) {
            // Настройки для мобильных устройств
            gridApi.setGridOption('rowHeight', 40);
            gridApi.setGridOption('headerHeight', 35);
            gridApi.setGridOption('paginationPageSize', 10);
            gridApi.setGridOption('suppressHorizontalScroll', false);

            // Скрываем менее важные колонки
            this.hideMobileColumns(gridApi);

            // Включаем полноэкранный режим для фильтров
            gridApi.setGridOption('floatingFilter', false);

        } else if (isTablet) {
            // Настройки для планшетов
            gridApi.setGridOption('rowHeight', 45);
            gridApi.setGridOption('headerHeight', 40);
            gridApi.setGridOption('paginationPageSize', 15);
        }

        // Автоподгонка колонок
        setTimeout(() => {
            gridApi.autoSizeAllColumns();
        }, 100);
    }

    hideMobileColumns(gridApi) {
        // Список колонок для скрытия на мобильных
        const mobileHiddenColumns = [
            'user.login',
            'created_at',
            'updated_at',
            'merchant',
            'order_id',
            'comment'
        ];

        mobileHiddenColumns.forEach(field => {
            try {
                gridApi.setColumnsVisible([field], false);
            } catch (e) {
                // Колонка может не существовать
            }
        });
    }

    optimizeAllGrids() {
        // Оптимизируем все зарегистрированные гриды
        this.grids.forEach((gridApi, name) => {
            this.optimizeGridApi(gridApi);
        });

        // Оптимизируем контейнеры
        document.querySelectorAll('.ag-theme-alpine.ag-theme-dark').forEach(grid => {
            this.optimizeGrid(grid);
        });
    }

    // Методы для настройки touch-жестов
    enableTouchNavigation(gridApi) {
        if (!gridApi) return;

        // Включаем touch-события
        gridApi.setGridOption('suppressTouch', false);

        // Настройки для touch-устройств
        gridApi.setGridOption('touchLayout', true);
        gridApi.setGridOption('suppressContextMenu', this.isMobile);
    }

    // Добавление кастомных мобильных элементов управления
    addMobileControls(container, gridApi) {
        if (!this.isMobile || !container || !gridApi) return;

        const controlsContainer = document.createElement('div');
        controlsContainer.className = 'ag-grid-mobile-controls flex justify-between items-center p-2 bg-gray-800 border-b border-gray-700';

        // Кнопка экспорта
        const exportBtn = document.createElement('button');
        exportBtn.className = 'px-3 py-1 bg-blue-600 text-white rounded text-sm';
        exportBtn.textContent = 'Экспорт';
        exportBtn.onclick = () => {
            gridApi.exportDataAsCsv();
        };

        // Кнопка автоподгонки колонок
        const autoSizeBtn = document.createElement('button');
        autoSizeBtn.className = 'px-3 py-1 bg-green-600 text-white rounded text-sm';
        autoSizeBtn.textContent = 'Подогнать';
        autoSizeBtn.onclick = () => {
            gridApi.autoSizeAllColumns();
        };

        // Индикатор записей
        const recordsInfo = document.createElement('span');
        recordsInfo.className = 'text-gray-300 text-sm';

        const updateRecordsInfo = () => {
            const displayedRows = gridApi.getDisplayedRowCount();
            recordsInfo.textContent = `Записей: ${displayedRows}`;
        };

        gridApi.addEventListener('modelUpdated', updateRecordsInfo);
        updateRecordsInfo();

        controlsContainer.appendChild(exportBtn);
        controlsContainer.appendChild(recordsInfo);
        controlsContainer.appendChild(autoSizeBtn);

        container.parentNode.insertBefore(controlsContainer, container);
    }

    // Улучшенная прокрутка для больших таблиц
    enhanceScrolling(gridApi) {
        if (!gridApi || !this.isMobile) return;

        // Включаем виртуализацию
        gridApi.setGridOption('rowModelType', 'clientSide');
        gridApi.setGridOption('animateRows', false); // Отключаем анимации на мобильных

        // Оптимизируем буфер строк
        gridApi.setGridOption('rowBuffer', 5);
        gridApi.setGridOption('debounceVerticalScrollbar', true);
    }
}

// CSS стили для мобильной оптимизации
const mobileStyles = `
    .ag-grid-mobile {
        font-size: 12px !important;
    }

    .ag-grid-mobile .ag-header-cell {
        font-size: 10px !important;
        min-height: 35px !important;
        padding: 4px 8px !important;
    }

    .ag-grid-mobile .ag-cell {
        min-height: 40px !important;
        padding: 6px 8px !important;
        font-size: 12px !important;
    }

    .ag-grid-mobile .ag-paging-panel {
        font-size: 12px !important;
        padding: 8px !important;
    }

    .ag-grid-tablet .ag-cell {
        min-height: 45px !important;
        padding: 8px 12px !important;
    }

    /* Touch-friendly кнопки */
    .ag-grid-mobile .ag-paging-button {
        min-width: 44px !important;
        min-height: 44px !important;
        font-size: 14px !important;
    }

    /* Улучшенная видимость на мобильных */
    .ag-theme-alpine.ag-theme-dark.ag-grid-mobile {
        --ag-border-color: #374151;
        --ag-row-border-color: #374151;
    }

    /* Горизонтальная прокрутка с индикатором */
    .ag-grid-mobile .ag-body-horizontal-scroll {
        height: 12px !important;
        background: #374151 !important;
    }

    .ag-grid-mobile .ag-body-horizontal-scroll-viewport {
        background: #6b7280 !important;
        border-radius: 6px !important;
    }

    /* Стили для мобильных контролов */
    .ag-grid-mobile-controls {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    /* Responsive колонки */
    @media (max-width: 480px) {
        .ag-grid-mobile .ag-cell {
            min-width: 80px !important;
        }

        .ag-grid-mobile .ag-header-cell {
            min-width: 80px !important;
        }
    }

    /* Touch feedback */
    .ag-theme-alpine.ag-theme-dark .ag-row:active {
        background-color: rgba(59, 130, 246, 0.1) !important;
    }

    .ag-theme-alpine.ag-theme-dark .ag-header-cell:active {
        background-color: rgba(59, 130, 246, 0.1) !important;
    }
`;

// Добавляем стили в DOM
const styleElement = document.createElement('style');
styleElement.textContent = mobileStyles;
document.head.appendChild(styleElement);

// Создаем глобальный экземпляр
window.mobileOptimizer = new MobileOptimizer();

export default MobileOptimizer;
