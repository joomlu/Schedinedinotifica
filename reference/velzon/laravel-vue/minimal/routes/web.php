<?php

use App\Http\Controllers\VelzonRoutesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::controller(VelzonRoutesController::class)->group(function () {

        // dashboards
        Route::get('/', 'dashboard');
        Route::get('/dashboard/analytics', 'dashboard_analytics');
        Route::get('/dashboard/crm', 'dashboard_crm');
        Route::get('/dashboard/crypto', 'dashboard_crypto');
        Route::get('/dashboard/job', 'dashboard_job');
        Route::get('/dashboard/nft', 'dashboard_nft');
        Route::get('/dashboard/projects', 'dashboard_projects');
        Route::get('/dashboard/blog', 'dashboard_blog');

        // apps calendar route
        Route::get("/calendar", "calendar");
        Route::get("/month-grid", "month_grid");

        // apps chat route
        Route::get("/chat", "chat");

        // apps file manager route
        Route::get("/apps-file-manager", "apps_file_manager");

        // apps todo route
        Route::get("/apps-todo", "apps_todo");

        // apps email routes
        Route::get("/mailbox", "mailbox");
        Route::get("/email/email-basic", "email_basic");
        Route::get("/email/email-ecommerce", "email_ecommerce");

        // apps nft routes
        Route::get("/apps/nft-auction", "nft_auction");
        Route::get("/apps/nft-collection", "nft_collection");
        Route::get("/apps/nft-create", "nft_create");
        Route::get("/apps/nft-creators", "nft_creators");
        Route::get("/apps/nft-explore", "nft_explore");
        Route::get("/apps/nft-item-detail", "nft_item");
        Route::get("/apps/nft-marketplace", "nft_marketplace");
        Route::get("/apps/nft-ranking", "nft_ranking");
        Route::get("/apps/nft-wallet", "nft_wallet");

        // apps project routes
        Route::get("/apps/projects-list", "projects_list");
        Route::get("/apps/projects-overview", "projects_overview");
        Route::get("/apps/projects-create", "projects_create");

        // apps task routes
        Route::get("/apps/tasks-details", "tasks_details");
        Route::get("/apps/tasks-kanban", "tasks_kanban");
        Route::get("/apps/tasks-list-view", "tasks_list_view");

        // apps tickets routes
        Route::get("/apps/tickets-details", "tickets_details");
        Route::get("/apps/tickets-list", "tickets_list");

        // apps other routes
        Route::get("/apps/crm-contacts", "crm_contacts");
        Route::get("/apps/crm-companies", "crm_companies");
        Route::get("/apps/crm-deals", "crm_deals");
        Route::get("/apps/crm-leads", "crm_leads");

        // apps ecommerce routes
        Route::get("/ecommerce/customers", "ecommerce_customers");
        Route::get("/ecommerce/products", "ecommerce_products");
        Route::get("/ecommerce/product-details", "ecommerce_product_details");
        Route::get("/ecommerce/orders", "ecommerce_orders");
        Route::get("/ecommerce/order-details", "ecommerce_order_details");
        Route::get("/ecommerce/add-product", "ecommerce_add_product");
        Route::get("/ecommerce/shopping-cart", "ecommerce_shopping_cart");
        Route::get("/ecommerce/checkout", "ecommerce_checkout");
        Route::get("/ecommerce/sellers", "ecommerce_sellers");
        Route::get("/ecommerce/seller-details", "ecommerce_seller_details");

        // apps crypto routes
        Route::get("/crypto/buy-sell", "crypto_buy_sell");
        Route::get("/crypto/kyc", "crypto_kyc");
        Route::get("/crypto/ico", "crypto_ico");
        Route::get("/crypto/orders", "crypto_orders");
        Route::get("/crypto/wallet", "crypto_wallet");
        Route::get("/crypto/transactions", "crypto_transactions");

        // apps invoice routes
        Route::get("/invoices/detail", "invoices_detail");
        Route::get("/invoices/list", "invoices_list");
        Route::get("/invoices/create", "invoices_create");

        // apps jobs routes
        Route::get("/jobs/application", "jobs_application");
        Route::get("/jobs/candidate-grid", "jobs_candidate_grid");
        Route::get("/jobs/candidate-lists", "jobs_candidate_lists");
        Route::get("/jobs/categories", "jobs_categories");
        Route::get("/jobs/companies-list", "jobs_companies_list");
        Route::get("/jobs/details", "jobs_details");
        Route::get("/jobs/grid-lists", "jobs_grid");
        Route::get("/jobs/lists", "jobs_lists");
        Route::get("/jobs/new", "jobs_new");
        Route::get("/jobs/statistics", "jobs_statistics");

        // apps api key route
        Route::get("/apps-api-key", "apps_api_key");

        // ui routes
        Route::get("/ui/alerts", "ui_alerts");
        Route::get("/ui/buttons", "ui_buttons");
        Route::get("/ui/cards", "ui_cards");
        Route::get("/ui/carousel", "ui_carousel");
        Route::get("/ui/dropdowns", "ui_dropdowns");
        Route::get("/ui/grid", "ui_grid");
        Route::get("/ui/images", "ui_images");
        Route::get("/ui/modals", "ui_modals");
        Route::get("/ui/offcanvas", "ui_offcanvas");
        Route::get("/ui/progress", "ui_progress");
        Route::get("/ui/placeholders", "ui_placeholders");
        Route::get("/ui/accordions", "ui_accordions");
        Route::get("/ui/tabs", "ui_tabs");
        Route::get("/ui/typography", "ui_typography");
        Route::get("/ui/embed-video", "ui_embed_video");
        Route::get("/ui/ribbons", "ui_ribbons");
        Route::get("/ui/lists", "ui_lists");
        Route::get("/ui/links", "ui_links");
        Route::get("/ui/utilities", "ui_utilities");
        Route::get("/ui/notifications", "ui_notifications");
        Route::get("/ui/general", "ui_general");
        Route::get("/ui/colors", "ui_colors");
        Route::get("/ui/badges", "ui_badges");
        Route::get("/ui/media", "ui_media");

        // widget route
        Route::get("/widgets", "widgets");

        // icons route
        Route::get("/icons/boxicons", "icons_boxicons");
        Route::get("/icons/materialdesign", "icons_materialdesign");
        Route::get("/icons/feather", "icons_feather");
        Route::get("/icons/lineawesome", "icons_lineawesome");
        Route::get("/icons/remix", "icons_remix");
        Route::get("/icons/crypto", "icons_crypto");

        // tables route
        Route::get("/tables/basic", "tables_basic");
        Route::get("/tables/gridjs", "tables_gridjs");

        // forms route
        Route::get("/form/advanced", "form_advanced");
        Route::get("/form/elements", "form_elements");
        Route::get("/form/layouts", "form_layouts");
        Route::get("/form/editors", "form_editors");
        Route::get("/form/file-uploads", "form_file_uploads");
        Route::get("/form/validation", "form_validation");
        Route::get("/form/wizard", "form_wizard");
        Route::get("/form/masks", "form_masks");
        Route::get("/form/pickers", "form_pickers");
        Route::get("/form/range-sliders", "form_range_sliders");
        Route::get("/form/select", "form_select");
        Route::get("/form/checkboxs-radios", "form_checkboxs_radios");

        // landing routes
        Route::get("/landing", "landing");
        Route::get("/nft-landing", "nft_landing");
        Route::get("/job-landing", "job_landing");

        // pages routes
        Route::get("/pages/starter", "pages_starter");
        Route::get("/pages/profile", "pages_profile");
        Route::get("/pages/profile-setting", "pages_profile_setting");
        Route::get("/pages/maintenance", "pages_maintenance");
        Route::get("/pages/coming-soon", "pages_coming_soon");
        Route::get("/pages/timeline", "pages_timeline");
        Route::get("/pages/faqs", "pages_faqs");
        Route::get("/pages/pricing", "pages_pricing");
        Route::get("/pages/team", "pages_team");
        Route::get("/pages/search-results", "pages_search_results");
        Route::get("/pages/sitemap", "pages_sitemap");
        Route::get("/pages/privacy-policy", "pages_privacy_policy");
        Route::get("/pages/term-conditions", "pages_term_conditions");
        Route::get("/pages/blogs/list-view", "pages_blog_list");
        Route::get("/pages/blogs/grid-view", "pages_blog_grid");
        Route::get("/pages/blogs/overview", "pages_blog_overview");

        // charts routes
        Route::get("/charts/chartjs", "charts_chartjs");
        Route::get("/charts/echart", "charts_echart");
        Route::get("/charts/apex-line", "charts_apex_line");
        Route::get("/charts/apex-area", "charts_apex_area");
        Route::get("/charts/apex-bar", "charts_apex_bar");
        Route::get("/charts/apex-column", "charts_apex_column");
        Route::get("/charts/apex-mixed", "charts_apex_mixed");
        Route::get("/charts/apex-range-area", "charts_apex_range");
        Route::get("/charts/apex-funnel", "charts_apex_funnel");
        Route::get("/charts/apex-candlestick", "charts_apex_candlestick");
        Route::get("/charts/apex-boxplot", "charts_apex_boxplot");
        Route::get("/charts/apex-bubble", "charts_apex_bubble");
        Route::get("/charts/apex-scatter", "charts_apex_scatter");
        Route::get("/charts/apex-heatmap", "charts_apex_heatmap");
        Route::get("/charts/apex-treemap", "charts_apex_treemap");
        Route::get("/charts/apex-pie", "charts_apex_pie");
        Route::get("/charts/apex-radialbar", "charts_apex_radialbar");
        Route::get("/charts/apex-radar", "charts_apex_radar");
        Route::get("/charts/apex-polararea", "charts_apex_polararea");
        Route::get("/charts/apex-slope", "charts_apex_slope");

        // advance ui route
        Route::get("/advance-ui/animation", "advance_ui_animation");
        Route::get("/advance-ui/highlight", "advance_ui_highlight");
        Route::get("/advance-ui/scrollbar", "advance_ui_scrollbar");
        Route::get("/advance-ui/scrollspy", "advance_ui_scrollspy");
        Route::get("/advance-ui/sweetalerts", "advance_ui_sweetalerts");
        Route::get("/advance-ui/swiper", "advance_ui_swiper");

        // auth sample page routes
        Route::get("/auth/signin-basic", "auth_signin_basic");
        Route::get("/auth/signin-cover", "auth_signin_cover");
        Route::get("/auth/signup-basic", "auth_signup_basic");
        Route::get("/auth/signup-cover", "auth_signup_cover");
        Route::get("/auth/reset-pwd-basic", "auth_reset_pwd_basic");
        Route::get("/auth/reset-pwd-cover", "auth_reset_pwd_cover");
        Route::get("/auth/create-pwd-basic", "auth_create_pwd_basic");
        Route::get("/auth/create-pwd-cover", "auth_create_pwd_cover");
        Route::get("/auth/lockscreen-basic", "auth_lockscreen_basic");
        Route::get("/auth/lockscreen-cover", "auth_lockscreen_cover");
        Route::get("/auth/twostep-basic", "auth_twostep_basic");
        Route::get("/auth/twostep-cover", "auth_twostep_cover");
        Route::get("/auth/404", "auth_404");
        Route::get("/auth/500", "auth_500");
        Route::get("/auth/404-basic", "auth_404_basic");
        Route::get("/auth/404-cover", "auth_404_cover");
        Route::get("/auth/ofline", "auth_ofline");
        Route::get("/auth/logout-basic", "auth_logout_basic");
        Route::get("/auth/logout-cover", "auth_logout_cover");
        Route::get("/auth/success-msg-basic", "auth_success_msg_basic");
        Route::get("/auth/success-msg-cover", "auth_success_msg_cover");

        // maps routes
        Route::get("/maps/amcharts", "maps_amcharts");
        Route::get("/maps/google", "maps_google");
        Route::get("/maps/leaflet", "maps_leaflet");
    });
});
