<?php $__env->startSection('title', translate('messages.Reels_List')); ?>
<?php $__env->startSection('reels', 'active'); ?>
<?php $__env->startSection('reels_list', 'active'); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php
    $storeLabel = translate('messages.Restaurant');
    $storeInformationLabel = $storeLabel . ' ' . translate('messages.information');
    $allStoreReelsListLabel = translate('messages.All') . ' ' . $storeLabel . ' ' . translate('messages.Reels_List');
    $deletedStoreLabel = $storeLabel . ' ' . translate('messages.deleted');

    $overviewCards = [
        [
            'value' => $overview['total_reels'],
            'label' => translate('messages.Total_Reels'),
            'icon' => 'tio-video-camera-outlined',
            'color' => 'text-purple',
            'bg' => 'bg-purple bg-opacity-10',
        ],
        [
            'value' => $overview['total_views'],
            'label' => translate('messages.Total_Views'),
            'icon' => 'tio-invisible',
            'color' => 'text-info',
            'bg' => 'bg-info bg-opacity-10',
            'note' => $analytics['weekly_change']['views'] . ' ' . translate('messages.this_week'),
        ],
        [
            'value' => $overview['total_likes'],
            'label' => translate('messages.Total_Likes'),
            'icon' => 'tio-heart-outlined',
            'color' => 'text-danger',
            'bg' => 'bg-danger bg-opacity-10',
            'note' => $analytics['weekly_change']['likes'] . ' ' . translate('messages.this_week'),
        ],
        [
            'value' => $overview['total_store_visits'],
            'label' => translate('messages.Store_Visits'),
            'icon' => 'tio-home-vs-2-outlined',
            'color' => 'text-success',
            'bg' => 'bg-success bg-opacity-10',
            'note' => $analytics['weekly_change']['visits'] . ' ' . translate('messages.this_week'),
        ],
        [
            'value' => \App\CentralLogics\Helpers::format_currency($analytics['status_counts']['total_sale_amount'] ?? 0),
            'label' => translate('messages.Total_Sale_Amount'),
            'tooltip' => translate('messages.Total_order_value_from_Reel_Order_Now_purchases'),
            'icon' => null,
            'icon_image' => dynamicAsset('assets/admin/img/reels/total-sale-amount-icon.svg'),
            'color' => 'text-purple',
            'bg' => 'bg-purple-dark bg-opacity-10',
            'note' => $analytics['weekly_change']['sale_amount'] . ' ' . translate('messages.this_week'),
        ],
        [
            'value' => $analytics['status_counts']['total_sale'] ?? 0,
            'label' => translate('messages.Total_Sale'),
            'tooltip' => translate('messages.Total_orders_placed_using_the_Reel_Order_Now_button'),
            'icon' => null,
            'icon_image' => dynamicAsset('assets/admin/img/reels/total-sale-icon.svg'),
            'color' => 'text-warning',
            'bg' => 'bg-warning bg-opacity-10',
            'note' => $analytics['weekly_change']['sale'] . ' ' . translate('messages.this_week'),
        ],
    ];
?>

<?php $__env->startSection('content'); ?>
    <div class="content container-fluid">
        <div class="row g-3">
            <div class="col-12">
                <div class="card card-body">
                    <h4 class="mb-3"><?php echo e(translate('messages.Reels_Overview')); ?></h4>
                    <div class="row g-3">
                        <?php $__currentLoopData = $overviewCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-sm-6 col-lg-4">
                                <a href="#" class="h-100 d-block">
                                    <div class="p-3 rounded-10 d-flex justify-content-between align-items-start gap-2 flex-wrap overflow-wrap-anywhere h-100 <?php echo e($card['bg']); ?>">
                                        <div class="flex-grow-1">
                                            <h3 class="fs-20 mb-1"><?php echo e($card['value']); ?></h3>
                                            <p class="text-muted mb-0 d-flex align-items-center gap-1">
                                                <?php echo e($card['label']); ?>

                                                <?php if(!empty($card['tooltip'])): ?>
                                                    <span class="form-label-secondary text-dark" data-toggle="tooltip" data-placement="top" data-title="<?php echo e($card['tooltip']); ?>">
                                                        <i class="tio-info-outined"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </p>
                                            <?php if(!empty($card['note'])): ?>
                                                <small class="text-muted fs-12"><?php echo e($card['note']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-shrink-0 bg-white-n p-2 rounded-10 lh--1">
                                            <?php if(!empty($card['icon_image'])): ?>
                                                <img width="20" src="<?php echo e($card['icon_image']); ?>" alt="">
                                            <?php else: ?>
                                                <i class="<?php echo e($card['icon']); ?> <?php echo e($card['color']); ?> fs-20"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header flex-wrap gap-3 border-0 pb-0">
                        <h4 class="mb-0"><?php echo e(translate('messages.Customer_Engagement')); ?></h4>
                        <div class="d-flex gap-3 align-items-center flex-wrap justify-content-center justify-content-sm-start">
                            <div class="bg-light rounded p-2 d-flex gap-4 align-items-center">
                                <span class="flex-shrink-0 fs-12"><i class="tio-record fs-16 text-info"></i> <?php echo e(translate('messages.View')); ?></span>
                                <input type="checkbox" class="chart-toggle" data-series="views" checked>
                            </div>
                            <div class="bg-light rounded p-2 d-flex gap-4 align-items-center">
                                <span class="flex-shrink-0 fs-12"><i class="tio-record fs-16 text-success"></i> <?php echo e(translate('messages.Likes')); ?></span>
                                <input type="checkbox" class="chart-toggle" data-series="likes" checked>
                            </div>
                        <div class="bg-light rounded p-2 d-flex gap-4 align-items-center">
                            <span class="flex-shrink-0 fs-12"><i class="tio-record fs-16 text-warning"></i> <?php echo e(translate('messages.Sell')); ?></span>
                            <input type="checkbox" class="chart-toggle" data-series="sell" checked>
                        </div>
                        </div>
                    </div>
                    <div class="card-body px-1 px-sm-2 py-0">
                        <div id="customer-engagement-chart"></div>
                    </div>
                </div>
            </div>

            <div class="col-12" id="reels-list-section">
                <h2 class="fs-20 d-flex gap-2 align-items-center text-capitalize lh-1 mb-20">
                    <span class="page-header-icon">
                        <i class="tio-filter-list fs-24"></i>
                    </span>
                    <span><?php echo e(translate('messages.Reels_List')); ?></span>
                    <span class="badge badge-soft-dark"><?php echo e($reels->total()); ?></span>
                </h2>
                <div class="card">
                    <div class="card-header py-1 border-0">
                        <div class="search--button-wrapper justify-content-end flex-wrap">
                            <h4 class="flex-grow-1 mb-0"><?php echo e($allStoreReelsListLabel); ?></h4>
                            <form class="search-form min--260" action="<?php echo e(route('admin.reels.index')); ?>" method="GET">
                                <?php $__currentLoopData = request()->except(['search', 'page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(is_array($value)): ?>
                                        <?php $__currentLoopData = $value; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <input type="hidden" name="<?php echo e($key); ?>[]" value="<?php echo e($item); ?>">
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <div class="input-group input--group">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control h--40px"
                                        placeholder="<?php echo e(translate('messages.Search_here')); ?>" value="<?php echo e(request('search')); ?>" aria-label="Search">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                            </form>

                            <div class="hs-unfold mr-2">
                                <a class="btn btn-outline-primary btn-white filter-button-show h--40px js-hs-unfold-invoker px-4 w-max-content" href="javascript:;">
                                    <i class="tio-filter-list mr-1"></i> <?php echo e(translate('messages.Filter')); ?>

                                    <span class="badge badge-success badge-pill ml-1" id="filter_count"><?php echo e($filterCount ?: ''); ?></span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php echo $__env->make('reelsmodule::admin.reels.partials._table', [
                        'reels' => $reels,
                        'storeInformationLabel' => $storeInformationLabel,
                        'deletedStoreLabel' => $deletedStoreLabel,
                        'restaurantViewRoute' => 'admin.restaurant.view',
                        'reelStatusRoute' => 'admin.reels.status',
                        'reelEditRoute' => 'admin.reels.edit',
                        'reelDestroyRoute' => 'admin.reels.destroy',
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <?php if(count($reels) !== 0): ?>
                        <hr>
                    <?php endif; ?>
                    <div class="page-area">
                        <?php echo $reels->links(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="datatableFilterSidebar" class="hs-unfold-content_ sidebar sidebar-bordered sidebar-box-shadow initial-hidden w-500px max-w-100">
        <div class="card card-lg sidebar-card sidebar-footer-fixed">
            <div class="card-header">
                <h4 class="card-header-title"><?php echo e(translate('messages.Filter')); ?></h4>
                <a class="js-hs-unfold-invoker_ p-1 rounded-circle btn-sm btn-ghost-dark ml-2 filter-button-hide" href="javascript:;">
                    <i class="tio-clear tio-lg"></i>
                </a>
            </div>

            <form class="card-body sidebar-body sidebar-scrollbar" action="<?php echo e(route('admin.reels.index')); ?>" method="GET" id="reels_filter_form">
                <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">

                <div class="bg-light rounded p-xxl-20 p-3 mb-3 mb-sm-4">
                    <label class="form-label"><?php echo e($storeLabel); ?></label>
                    <select name="restaurant_ids[]" id="restaurant_ids" class="form-control js-select2-custom" multiple="multiple">
                        <?php $__currentLoopData = $restaurants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $restaurant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($restaurant->id); ?>" data-verified="<?php echo e((int) $restaurant->verified_seller); ?>" <?php echo e(in_array($restaurant->id, array_map('intval', (array) request('restaurant_ids', []))) ? 'selected' : ''); ?>>
                                <?php echo e($restaurant->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="bg-light rounded p-xxl-20 p-3 mb-3 mb-sm-4">
                    <label class="form-label"><?php echo e(translate('messages.Reel_Status')); ?></label>
                    <div class="py-2 px-3 rounded min-h-45px bg-white">
                        <div class="row g-1">
                            <?php
                                $selectedStatuses = (array) request('reel_status', ['all']);
                            ?>
                            <?php $__currentLoopData = ['all', 'upcoming', 'live', 'expired', 'deactivated']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-sm-6">
                                    <label class="custom_checkbox d-flex align-items-center gap-1 flex-grow-1 m-0 pt-2px">
                                        <input type="checkbox" value="<?php echo e($status); ?>" name="reel_status[]" <?php echo e(in_array($status, $selectedStatuses) ? 'checked' : ''); ?>>
                                        <span class="label-text"><?php echo e(translate('messages.' . $status)); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                <div class="bg-light rounded p-xxl-20 p-3 mb-3 mb-sm-4">
                    <label class="form-label"><?php echo e(translate('messages.Sort_By')); ?></label>
                    <div class="py-2 px-3 rounded min-h-45px bg-white">
                        <div class="row g-1">
                            <?php $__currentLoopData = [
                                'all' => 'all',
                                'most_viewed' => 'most_viewed',
                                'most_liked' => 'most_liked',
                                'most_store_visit' => 'most_store_visit',
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sortValue => $sortLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-sm-6">
                                    <label class="custom-control custom-radio mb-0">
                                        <input type="radio" class="custom-control-input" value="<?php echo e($sortValue); ?>" name="sort_by" <?php echo e(request('sort_by', 'all') === $sortValue ? 'checked' : ''); ?>>
                                        <span class="custom-control-label fs-12 text-capitalize"><?php echo e(translate('messages.' . $sortLabel)); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                <div class="bg-light rounded p-xxl-20 p-3 mb-3 mb-sm-4">
                    <div class="d-flex flex-column gap-3 gap-sm-4">
                        <div>
                            <label class="form-label"><?php echo e(translate('messages.Reel_Upload_Date')); ?></label>
                            <select name="filter_date" id="filter_date" class="form-control custom-select date-type-select">
                                <option value="all_time" <?php echo e(request('filter_date', 'all_time') === 'all_time' ? 'selected' : ''); ?>><?php echo e(translate('messages.All_Time')); ?></option>
                                <option value="this_week" <?php echo e(request('filter_date') === 'this_week' ? 'selected' : ''); ?>><?php echo e(translate('messages.This_Week')); ?></option>
                                <option value="this_month" <?php echo e(request('filter_date') === 'this_month' ? 'selected' : ''); ?>><?php echo e(translate('messages.This_Month')); ?></option>
                                <option value="custom" <?php echo e(request('filter_date') === 'custom' ? 'selected' : ''); ?>><?php echo e(translate('messages.Custom')); ?></option>
                            </select>
                        </div>
                        <div id="custom_date_wrapper" class="<?php echo e(request('filter_date') === 'custom' ? '' : 'd-none'); ?>">
                            <div class="mb-3">
                                <label class="form-label"><?php echo e(translate('messages.Start_Date')); ?></label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo e(request('start_date')); ?>" max="<?php echo e(request('end_date')); ?>">
                            </div>
                            <div>
                                <label class="form-label"><?php echo e(translate('messages.End_Date')); ?></label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo e(request('end_date')); ?>" min="<?php echo e(request('start_date')); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer sidebar-footer">
                    <div class="row gx-2">
                        <div class="col">
                            <a href="<?php echo e(route('admin.reels.index')); ?>" class="btn btn-block btn--reset" id="reset"><?php echo e(translate('messages.Reset')); ?></a>
                        </div>
                        <div class="col">
                            <button type="submit" class="btn btn-block btn-primary"><?php echo e(translate('messages.Filter')); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php echo $__env->make('reelsmodule::shared._reels-details-offcanvas', [
        'reels' => $reels,
        'deletedStoreLabel' => $deletedStoreLabel,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script src="<?php echo e(dynamicAsset('assets/admin/apexcharts/apexcharts.min.js')); ?>"></script>
    <?php echo $__env->make('reelsmodule::shared._reels-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
        "use strict";

        let customerEngagementChart = null;

        function formatCompactValue(val) {
            if (val >= 1000000) {
                return (val / 1000000).toFixed(1).replace('.0', '') + 'M';
            }

            if (val >= 1000) {
                return (val / 1000).toFixed(1).replace('.0', '') + 'K';
            }

            return String(val);
        }

        function initCustomerEngagementChart(categories = [], chartSeries = {}) {
            const chartElement = document.getElementById('customer-engagement-chart');
            if (!chartElement) return;

            if (customerEngagementChart) {
                customerEngagementChart.destroy();
                customerEngagementChart = null;
            }

            const options = {
                series: [
                    { name: 'views', data: chartSeries.views || [] },
                    { name: 'likes', data: chartSeries.likes || [] },
                    { name: 'sell', data: chartSeries.sell || [] },
                ],
                chart: {
                    height: 360,
                    type: 'line',
                    toolbar: { show: false }
                },
                colors: ['#2563eb', '#10b981', '#f59e0b'],
                stroke: {
                    width: 3,
                    curve: 'smooth'
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                xaxis: { categories: categories },
                yaxis: {
                    min: 0,
                    tickAmount: 4,
                    labels: {
                        formatter: function (val) {
                            return formatCompactValue(val);
                        }
                    }
                },
                markers: {
                    size: 0,
                    hover: {
                        size: 5
                    }
                },
                grid: { strokeDashArray: 4 },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (val) {
                            return formatCompactValue(val);
                        }
                    }
                }
            };

            customerEngagementChart = new ApexCharts(chartElement, options);
            customerEngagementChart.render().then(function () {
                $('.chart-toggle').each(function () {
                    const seriesName = $(this).data('series');
                    if (!$(this).is(':checked')) {
                        customerEngagementChart.toggleSeries(seriesName);
                    }
                });
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            initCustomerEngagementChart(<?php echo json_encode($analytics['view_trend_categories'], 15, 512) ?>, <?php echo json_encode($analytics['chart_series'], 15, 512) ?>);

            $(document).on('change', '.chart-toggle', function () {
                if (!customerEngagementChart) return;
                customerEngagementChart.toggleSeries($(this).data('series'));
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\Oorzo\Admin panel new install V9.3\Modules/ReelsModule\Resources/views/admin/reels/index.blade.php ENDPATH**/ ?>