<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?>

<?php
$is_app_mode =
    !empty($is_app_mode) ||
    $this->input->get('app') == 1 ||
    $this->input->post('app') == 1 ||
    $this->input->get('mobile') == 1 ||
    $this->input->post('mobile') == 1;

$app_language =
    !empty($app_language)
        ? $app_language
        : (
            $this->input->get('app_lang', true) ?:
            (
                $this->input->post('app_lang', true) ?:
                $Settings->selected_language
            )
        );

$app_query = $is_app_mode
    ? '?app=1&app_lang=' . rawurlencode($app_language)
    : '';

$active_language = strtolower(trim((string) $app_language));

$is_myanmar =
    in_array($active_language, ['myanmar','burmese','mm','my','my-mm'], true) ||
    stripos($active_language, 'myanmar') !== false ||
    stripos($active_language, 'burmese') !== false;

$T = function ($mm, $en) use ($is_myanmar) {
    return $is_myanmar ? $mm : $en;
};

$current_base_unit_id =
    !empty($product->base_unit_id)
        ? (int) $product->base_unit_id
        : 0;

$current_base_unit_name =
    !empty($product->base_unit_name)
        ? $product->base_unit_name
        : '';

/*
 * Dual-unit products keep two independent stocks:
 *   1) primary/base quantity (for example ounce/viss)
 *   2) secondary count quantity (for example piece/coil)
 * The secondary unit must never become a packaging-chain level because
 * there is no fixed conversion when every piece/coil has a different weight.
 */
$is_dual_unit =
    isset($product->is_dual_unit) &&
    (int) $product->is_dual_unit === 1;

$current_secondary_unit_id =
    !empty($product->secondary_unit_id)
        ? (int) $product->secondary_unit_id
        : 0;

$current_secondary_unit_name =
    !empty($product->secondary_unit_name)
        ? (string) $product->secondary_unit_name
        : '';

$units_for_js = [];
$weight_conversion_unit_ids = [];

foreach ($conversions as $conversion) {
    if (!empty($conversion->unit_id)) {
        $weight_conversion_unit_ids[(int) $conversion->unit_id] = true;
    }
}

foreach ($units as $unit) {
    if (
        $is_dual_unit &&
        $current_secondary_unit_id > 0 &&
        (int) $unit->id === $current_secondary_unit_id &&
        $current_secondary_unit_name === ''
    ) {
        $current_secondary_unit_name = (string) $unit->name;
    }

    $units_for_js[] = [
        'id'   => (int) $unit->id,
        'name' => (string) $unit->name,
        'code' => !empty($unit->code) ? (string) $unit->code : ''
    ];
}
?>

<style>
.content{padding-top:18px;background:#f4f7fb}
.uc-page{max-width:1180px;margin:0 auto}
.uc-card{overflow:hidden;margin-bottom:22px;border:1px solid #e5e7eb;border-radius:16px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.06)}
.uc-header{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:14px;padding:18px 22px;border-bottom:1px solid #e6edf3;background:linear-gradient(135deg,#fff 0%,#f7fbfc 100%)}
.uc-title{display:flex;align-items:center;gap:12px;margin:0;color:#111827;font-size:20px;font-weight:900}
.uc-title-icon{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;flex:0 0 44px;border-radius:12px;background:#e6f4f1;color:#0f766e}
.uc-subtitle{margin-top:5px;color:#64748b;font-size:15px}
.uc-back-btn{display:inline-flex!important;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:8px 13px!important;border:1px solid #d1d5db!important;border-radius:9px!important;background:#f8fafc!important;color:#334155!important;font-weight:800;text-decoration:none!important;white-space:nowrap}
.uc-body{padding:22px 24px 0}

.uc-alert{margin-bottom:16px;padding:12px 14px;border-radius:10px;font-size:13px;font-weight:700}
.uc-alert-danger{border:1px solid #fecaca;background:#fff1f2;color:#b91c1c}
.uc-alert-success{border:1px solid #bbf7d0;background:#f0fdf4;color:#15803d}

.uc-product-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:17px}
.uc-info-box{min-width:0;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc}
.uc-info-label{display:block;margin-bottom:6px;color:#64748b;font-size:15px;font-weight:800}
.uc-info-value{color:#0f172a;font-size:14px;font-weight:900;overflow-wrap:anywhere}
.uc-required{color:#dc2626}

.uc-base-readonly-wrap{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:7px}
.uc-base-readonly-value{display:flex;align-items:center;justify-content:center;min-width:0;min-height:48px;padding:8px 10px;border:1px solid #bfdbfe;border-radius:9px;background:#eff6ff;color:#1d4ed8;font-size:13px;font-weight:900;line-height:1.4;text-align:center;word-break:break-word}
.uc-base-edit-btn{display:inline-flex!important;align-items:center;justify-content:center;gap:5px;min-height:48px;padding:8px 11px!important;border:1px solid #bfdbfe!important;border-radius:9px!important;background:#fff!important;color:#1d4ed8!important;font-weight:900}
.uc-base-edit-btn:hover{background:#eff6ff!important}

.uc-dual-banner{display:grid;grid-template-columns:minmax(220px,.8fr) minmax(0,1.5fr);gap:16px;align-items:center;margin:-1px 0 17px;padding:16px 18px;border:1px solid #c4b5fd;border-radius:13px;background:linear-gradient(135deg,#f5f3ff 0%,#faf5ff 100%)}
.uc-dual-heading{display:flex;align-items:center;gap:10px;color:#5b21b6;font-size:16px;font-weight:900}
.uc-dual-badge{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:7px 11px;border-radius:999px;background:#6d28d9;color:#fff;font-size:13px;font-weight:900;white-space:nowrap}
.uc-dual-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
.uc-dual-detail{padding:10px 12px;border:1px solid #ddd6fe;border-radius:10px;background:rgba(255,255,255,.88)}
.uc-dual-detail-label{display:block;margin-bottom:4px;color:#7c3aed;font-size:12px;font-weight:900}
.uc-dual-detail-value{color:#3b0764;font-size:14px;font-weight:900;overflow-wrap:anywhere}
.uc-dual-note{grid-column:1/-1;display:flex;align-items:flex-start;gap:7px;padding:9px 11px;border-radius:9px;background:#fff;color:#6b21a8;font-size:13px;font-weight:800;line-height:1.7}
.uc-dual-note i{margin-top:4px}
.uc-secondary-unit-select{height:42px!important;border-color:#c4b5fd!important;background:#fff!important;color:#3b0764!important;font-weight:800}

.uc-select{width:100%;height:44px!important;padding:7px 10px!important;border:1px solid #cbd5e1!important;border-radius:9px!important;background:#fff!important;color:#111827!important;box-shadow:none!important}

.uc-card .select2-container,
#addPackagingModal .select2-container,
#editBaseUnitModal .select2-container{display:block!important;width:100%!important}

.uc-card .select2-container .select2-choice,
#addPackagingModal .select2-container .select2-choice{width:100%!important;height:44px!important;border:0!important;border-radius:8px!important;background:transparent!important;box-shadow:none!important}

.uc-card .select2-container .select2-choice>.select2-chosen,
#addPackagingModal .select2-container .select2-choice>.select2-chosen{line-height:42px!important;color:#0f172a!important;font-weight:800}

.uc-card .select2-container .select2-choice .select2-arrow,
#addPackagingModal .select2-container .select2-choice .select2-arrow{border-left:0!important;background:transparent!important}

.uc-card .select2-container--default .select2-selection--single,
#addPackagingModal .select2-container--default .select2-selection--single{width:100%!important;height:44px!important;border:0!important;border-radius:8px!important;background:transparent!important;box-shadow:none!important}

.uc-card .select2-container--default .select2-selection--single .select2-selection__rendered,
#addPackagingModal .select2-container--default .select2-selection--single .select2-selection__rendered{padding-left:4px!important;padding-right:32px!important;line-height:42px!important;color:#0f172a!important;font-weight:800}

.uc-card .select2-container--default .select2-selection--single .select2-selection__arrow,
#addPackagingModal .select2-container--default .select2-selection--single .select2-selection__arrow{height:42px!important}

.select2-drop,.select2-dropdown,.select2-container--open,.select2-drop-active{z-index:10650!important}
.select2-search input,.select2-search__field{border-radius:7px!important}

.uc-intro{margin-bottom:17px;padding:15px 17px;border:1px solid #99f6e4;border-radius:13px;background:#f0fdfa;color:#115e59}
.uc-intro-title{display:flex;align-items:center;gap:8px;margin-bottom:7px;font-size:14px;font-weight:900}
.uc-intro-text{font-size:15px;line-height:1.8}
.uc-example{margin-top:9px;padding:10px 12px;border-radius:9px;background:rgba(255,255,255,.82);font-weight:800;line-height:1.8}

.uc-section{padding:18px;border:1px solid #e5e7eb;border-radius:14px;background:#fff}
.uc-section-header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
.uc-section-title{margin:0;color:#0f172a;font-size:15px;font-weight:900}
.uc-add-btn{min-height:40px;padding:8px 13px!important;border-color:#0f766e!important;border-radius:9px!important;background:#0f766e!important;color:#fff!important;font-weight:800!important}

.uc-fields-header{display:grid;grid-template-columns:minmax(200px,1fr) minmax(200px,1fr) minmax(160px,.7fr);gap:12px;margin:0 15px 9px}
.uc-field-header{padding:0 9px 5px;color:#64748b;font-size:15px;font-weight:900}
.uc-field-header:last-child{text-align:center}

.uc-conversion-list{display:flex;flex-direction:column;gap:12px}
.uc-conversion-row{position:relative;padding:15px;border:1px solid #e2e8f0;border-radius:13px;background:#f8fafc}
.uc-step{display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:30px;margin-bottom:10px;padding:0 9px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-size:15px;font-weight:900}

.uc-sentence{display:grid;grid-template-columns:minmax(200px,1fr) minmax(200px,1fr) minmax(160px,.7fr);gap:12px;align-items:stretch}

.uc-field-box{position:relative;display:flex;align-items:center;width:100%;min-width:0;min-height:58px;padding:7px 12px;border:1px solid #bfdbfe;border-radius:10px;background:#eff6ff;box-sizing:border-box}
.uc-first-unit-box{background:#eff6ff}
.uc-parent-unit{display:flex;align-items:center;justify-content:center;width:100%;min-height:42px;color:#1d4ed8;font-size:15px;font-weight:900;line-height:1.4;text-align:center;white-space:normal;word-break:break-word}

.uc-readonly-box{justify-content:center;background:#f8fafc;border-color:#cbd5e1}
.uc-readonly-value{width:100%;color:#0f172a;font-size:15px;font-weight:900;line-height:1.4;text-align:center;word-break:break-word}

.uc-preview{display:flex;align-items:flex-start;gap:8px;margin-top:11px;padding:10px 12px;border:1px solid #99f6e4;border-radius:9px;background:#fff;color:#0f766e;font-size:15px;font-weight:800;line-height:1.7}
.uc-preview i{margin-top:3px}

.uc-row-footer{display:flex;justify-content:flex-end;gap:7px;margin-top:10px}
.uc-edit-btn{display:inline-flex!important;align-items:center;justify-content:center;gap:6px;min-height:39px;padding:7px 11px!important;border:1px solid #bfdbfe!important;border-radius:9px!important;background:#eff6ff!important;color:#1d4ed8!important;font-weight:800}
.uc-remove-btn{display:inline-flex!important;align-items:center;justify-content:center;gap:6px;min-height:39px;padding:7px 11px!important;border:1px solid #fecaca!important;border-radius:9px!important;background:#fff1f2!important;color:#dc2626!important;font-weight:800}

.uc-empty{padding:25px;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;color:#64748b;text-align:center;line-height:1.8}
.uc-empty-icon{margin-bottom:7px;color:#94a3b8;font-size:25px}

.uc-result-box{margin-top:16px;padding:15px;border:1px solid #bfdbfe;border-radius:12px;background:#eff6ff}
.uc-result-title{margin-bottom:9px;color:#1e40af;font-size:15px;font-weight:900}
.uc-result-line{padding:5px 0;color:#334155;font-size:15px;font-weight:800}
.uc-result-line i{margin-right:5px;color:#16a34a}

.uc-actions{position:sticky;bottom:0;z-index:20;display:flex;justify-content:flex-end;gap:9px;margin:20px -24px 0;padding:14px 24px;border-top:1px solid #e5e7eb;background:#fff;box-shadow:0 -8px 18px rgba(15,23,42,.04)}
.uc-actions .btn{min-height:42px;padding:9px 16px!important;border-radius:9px!important;font-weight:800}
.uc-save-btn{border-color:#0f766e!important;background:#0f766e!important;color:#fff!important}


/* =========================================================
   AUTO SAVE STATUS
========================================================= */
.uc-autosave-overlay{
    position:fixed;
    inset:0;
    z-index:20000;
    display:none;
    align-items:center;
    justify-content:center;
    padding:20px;
    background:rgba(15,23,42,.28)
}
.uc-autosave-box{
    display:flex;
    align-items:center;
    gap:10px;
    min-width:190px;
    padding:14px 18px;
    border-radius:12px;
    background:#fff;
    color:#0f172a;
    font-size:13px;
    font-weight:900;
    box-shadow:0 18px 45px rgba(15,23,42,.22)
}
.uc-autosave-box i{color:#0f766e;font-size:16px}

#addPackagingModal .modal-dialog{max-width:620px}
#addPackagingModal .modal-content{overflow:visible;border:0;border-radius:14px;box-shadow:0 18px 50px rgba(15,23,42,.22)}
#addPackagingModal .modal-header{padding:16px 18px;border-bottom:1px solid #e5e7eb;background:#f8fafc;border-radius:14px 14px 0 0}
#addPackagingModal .modal-title{color:#0f172a;font-size:16px;font-weight:900}
#addPackagingModal .modal-title i{margin-right:6px;color:#0f766e}
#addPackagingModal .modal-body{padding:18px}
#addPackagingModal .modal-footer{padding:13px 18px;border-top:1px solid #e5e7eb}

.uc-modal-unit-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:16px}
.uc-modal-unit-row .uc-modal-form-group{margin-bottom:0}
.uc-modal-form-group{margin-bottom:16px}
.uc-modal-label{display:block;margin-bottom:7px;color:#334155;font-size:15px;font-weight:900}
.uc-modal-first-unit{display:flex;align-items:center;justify-content:center;min-height:48px;padding:10px 12px;border:1px solid #bfdbfe;border-radius:9px;background:#eff6ff;color:#1d4ed8;font-size:15px;font-weight:900;text-align:center}
.uc-modal-input-box{width:100%;min-height:48px;padding:2px 10px;border:1px solid #cbd5e1;border-radius:9px;background:#fff}
.uc-modal-input-box:focus-within{border-color:#0f766e;box-shadow:0 0 0 3px rgba(15,118,110,.10)}
.uc-modal-qty{width:100%;height:42px!important;border:0!important;background:transparent!important;color:#0f172a;font-size:16px;font-weight:900;text-align:center;box-shadow:none!important}
.uc-modal-preview{display:none;margin-top:4px;padding:11px 13px;border:1px solid #99f6e4;border-radius:9px;background:#f0fdfa;color:#0f766e;font-size:15px;font-weight:800;line-height:1.7}
.uc-modal-preview i{margin-right:6px}

@media (max-width:991px){
    .uc-fields-header,.uc-sentence{
        grid-template-columns:minmax(160px,1fr) minmax(180px,1fr) minmax(130px,.7fr)
    }
}

@media (max-width:767px){
    .content{padding-top:0}
    .uc-header{padding:11px 10px}
    .uc-title{gap:7px;font-size:14px}
    .uc-title-icon{width:34px;height:34px;flex-basis:34px;border-radius:9px;font-size:13px}
    .uc-subtitle{margin-top:3px;font-size:15px}
    .uc-back-btn span{display:none}
    .uc-back-btn{width:36px;min-width:36px;min-height:36px;padding:0!important}
    .uc-body{padding:10px 10px 0}

    .uc-product-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:6px!important;margin-bottom:10px}
    .uc-info-box{min-width:0;padding:8px 7px;border-radius:9px}
    .uc-info-label{margin-bottom:4px;font-size:15px;line-height:1.4}
    .uc-info-value{font-size:15px;line-height:1.5;word-break:break-word}
    .uc-base-readonly-wrap{grid-template-columns:minmax(0,1fr) 36px;gap:4px}
    .uc-base-readonly-value{min-height:38px;padding:5px 4px;font-size:15px}
    .uc-base-edit-btn{width:36px;min-width:36px;min-height:38px;padding:0!important;font-size:15px}
    .uc-base-edit-btn span{display:none}

    .uc-dual-banner{grid-template-columns:1fr;gap:9px;margin-bottom:10px;padding:10px;border-radius:10px}
    .uc-dual-heading{justify-content:space-between;font-size:14px}
    .uc-dual-badge{padding:5px 8px;font-size:12px}
    .uc-dual-details{grid-template-columns:repeat(2,minmax(0,1fr));gap:6px}
    .uc-dual-detail{padding:7px}
    .uc-dual-detail-label{font-size:11px}
    .uc-dual-detail-value{font-size:13px}
    .uc-dual-note{padding:7px;font-size:12px;line-height:1.55}

    .uc-intro{margin-bottom:10px;padding:10px;border-radius:10px}
    .uc-intro-title{margin-bottom:5px;font-size:15px}
    .uc-intro-text{font-size:15px;line-height:1.7}
    .uc-example{margin-top:7px;padding:8px;font-size:15px;line-height:1.6}

    .uc-section{padding:10px}
    .uc-section-header{display:flex!important;align-items:center;justify-content:space-between;gap:6px;margin-bottom:10px}
    .uc-section-title{flex:1;font-size:15px}
    .uc-add-btn{width:auto!important;min-height:35px;padding:5px 7px!important;font-size:15px}

    .uc-fields-header{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:5px!important;margin:0 0 5px!important}
    .uc-field-header{padding:0 2px 3px;font-size:15px;line-height:1.35; text-align:center;}
    .uc-field-header:last-child{text-align:center}

    .uc-conversion-list{gap:8px}
    .uc-conversion-row{padding:8px;border-radius:10px}
    .uc-step{min-width:24px;height:24px;margin-bottom:6px;padding:0 6px;font-size:15px}
    .uc-sentence{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:5px!important}
    .uc-field-box{display:flex!important;align-items:center;min-width:0;min-height:48px;padding:4px 5px;border-radius:8px}

    .uc-parent-unit,.uc-readonly-value{
        min-width:0;min-height:36px;display:flex;align-items:center;justify-content:center;
        font-size:15px;line-height:1.4;text-align:center
    }

    .uc-preview{margin-top:6px;padding:6px 7px;font-size:15px;line-height:1.5}
    .uc-row-footer{margin-top:6px;gap:5px}
    .uc-edit-btn,.uc-remove-btn{min-height:32px;padding:4px 7px!important;font-size:15px}

    .uc-result-box{margin-top:9px;padding:9px}
    .uc-result-title,.uc-result-line{font-size:15px}

    .uc-actions{display:grid!important;grid-template-columns:1fr 1fr;gap:7px;margin:12px -10px 0;padding:10px}
    .uc-actions .btn{width:100%;margin:0!important;font-size:15px}

    #addPackagingModal .modal-dialog{margin:14px;max-width:none}
    #addPackagingModal .modal-body{padding:14px}
    .uc-modal-unit-row{grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}
    .uc-modal-label{font-size:15px}
    .uc-modal-first-unit{font-size:15px}
    .uc-modal-input-box{min-width:0}
    #addPackagingModal .select2-container{min-width:0!important;width:100%!important}
}

<?php if ($is_app_mode) { ?>
.main-header,
.main-sidebar,
.main-footer,
.control-sidebar,
.breadcrumb{display:none!important}

.content-wrapper,
.right-side{
    min-height:100vh!important;
    margin-left:0!important;
    padding-top:0!important;
    background:#f4f7fb!important
}

.content{margin:0!important;padding:0 8px 8px!important}
.uc-page{max-width:100%!important}
.uc-card{margin-bottom:0!important;box-shadow:none!important}
<?php } ?>
</style>


<section class="content">
<div class="uc-page">
<div class="uc-card">

    <div class="uc-header" style="display:none;">
        <div>
            <h4 class="uc-title">
                <span class="uc-title-icon"><i class="fa fa-cubes"></i></span>
                <span>
                    <?= html_escape(
                        $T(
                            'ကုန်ပစ္စည်းထုပ်ပိုးပုံ သတ်မှတ်ရန်',
                            'Set Product Packaging'
                        )
                    ); ?>
                </span>
            </h4>

            <div class="uc-subtitle">
                <?= html_escape(
                    $product->name .
                    ' (' .
                    $product->code .
                    ')'
                ); ?>
            </div>
        </div>

        <a
            href="<?= site_url('products') . $app_query; ?>"
            class="uc-back-btn"
        >
            <i class="fa fa-arrow-left"></i>
            <span>
                <?= html_escape(
                    $T(
                        'ကုန်ပစ္စည်းစာရင်း',
                        'Product List'
                    )
                ); ?>
            </span>
        </a>
    </div>


    <div class="uc-body">

        <?php if (!empty($error)) { ?>
            <div class="uc-alert uc-alert-danger">
                <i class="fa fa-exclamation-circle"></i>
                <?= $error; ?>
            </div>
        <?php } ?>

        <?php if (!empty($message)) { ?>
            <div class="uc-alert uc-alert-success">
                <i class="fa fa-check-circle"></i>
                <?= $message; ?>
            </div>
        <?php } ?>


        <?= form_open(
            'products/unit_conversions/' . (int) $product->id,
            'id="unit_conversion_form" autocomplete="off"'
        ); ?>


        <?php if ($is_app_mode) { ?>
            <input type="hidden" name="app" value="1">
            <input
                type="hidden"
                name="app_lang"
                value="<?= html_escape($app_language); ?>"
            >
        <?php } ?>


        <!-- PRODUCT INFO -->
        <div class="uc-product-grid">

            <div class="uc-info-box">
                <div class="uc-info-label">
                    <?= html_escape(
                        $T(
                            'ကုန်ပစ္စည်းအမည်',
                            'Product Name'
                        )
                    ); ?>
                </div>

                <div class="uc-info-value">
                    <?= html_escape($product->name); ?>
                </div>
            </div>


            <div class="uc-info-box">
                <div class="uc-info-label">
                    <?= html_escape(
                        $T(
                            'ကုန်ပစ္စည်းကုဒ်',
                            'Product Code'
                        )
                    ); ?>
                </div>

                <div class="uc-info-value">
                    <?= html_escape($product->code); ?>
                </div>
            </div>


            <div class="uc-info-box">

                <div class="uc-info-label">
                    <?= html_escape(
                        $T(
                            $is_dual_unit
                                ? 'အခြေခံအလေးချိန်ယူနစ်'
                                : 'အငယ်ဆုံး ရေတွက်မည့်ယူနစ်',
                            $is_dual_unit
                                ? 'Primary Weight Unit'
                                : 'Smallest Counting Unit'
                        )
                    ); ?>

                    <span class="uc-required">*</span>
                </div>


                <div class="uc-base-readonly-wrap">

                    <div
                        id="base_unit_display"
                        class="uc-base-readonly-value"
                    >
                        <?= html_escape(
                            !empty($current_base_unit_name)
                                ? $current_base_unit_name
                                : $T(
                                    'ယူနစ်မရွေးရသေးပါ',
                                    'No unit selected'
                                )
                        ); ?>
                    </div>


                    <button
                        type="button"
                        id="edit_base_unit_btn"
                        class="btn uc-base-edit-btn"
                    >
                        <i class="fa fa-pencil"></i>

                        <span>
                            <?= html_escape(
                                $T(
                                    'ပြင်ရန်',
                                    'Edit'
                                )
                            ); ?>
                        </span>
                    </button>

                </div>


                <input
                    type="hidden"
                    name="base_unit_id"
                    id="base_unit_id"
                    value="<?= (int) $current_base_unit_id; ?>"
                    required
                >

            </div>

        </div>


        <?php if ($is_dual_unit) { ?>
            <!-- DUAL UNIT IDENTITY -->
            <div class="uc-dual-banner">

                <div class="uc-dual-heading">
                    <span>
                        <i class="fa fa-balance-scale"></i>
                        <?= html_escape(
                            $T(
                                'ယူနစ်နှစ်မျိုး သီးခြားတွက်သည့်ပစ္စည်း',
                                'Independent Dual Unit Product'
                            )
                        ); ?>
                    </span>

                    <span class="uc-dual-badge">
                        <i class="fa fa-check-circle"></i>
                        DUAL UNIT
                    </span>
                </div>

                <div class="uc-dual-details">

                    <div class="uc-dual-detail">
                        <span class="uc-dual-detail-label">
                            <?= html_escape(
                                $T(
                                    'အဓိကအလေးချိန်ယူနစ်',
                                    'Primary weight unit'
                                )
                            ); ?>
                        </span>

                        <div class="uc-dual-detail-value" id="dual_base_unit_display">
                            <?= html_escape(
                                !empty($current_base_unit_name)
                                    ? $current_base_unit_name
                                    : $T(
                                        'ယူနစ်မရွေးရသေးပါ',
                                        'No unit selected'
                                    )
                            ); ?>
                        </div>
                    </div>

                    <div class="uc-dual-detail">
                        <span class="uc-dual-detail-label">
                            <?= html_escape(
                                $T(
                                    'သီးခြားရေတွက်မည့်ယူနစ်',
                                    'Independent counting unit'
                                )
                            ); ?>
                        </span>

                        <select
                            name="secondary_unit_id"
                            id="secondary_unit_id"
                            class="form-control uc-secondary-unit-select"
                            required
                        >
                            <option value="">
                                <?= html_escape(
                                    $T(
                                        'လုံး/ခွေ ယူနစ်ရွေးချယ်ရန်',
                                        'Select piece/coil unit'
                                    )
                                ); ?>
                            </option>

                            <?php foreach ($units as $unit) { ?>
                                <?php if (
                                    (int) $unit->id !== $current_base_unit_id &&
                                    !isset($weight_conversion_unit_ids[(int) $unit->id])
                                ) { ?>
                                    <option
                                        value="<?= (int) $unit->id; ?>"
                                        <?= (int) $unit->id === $current_secondary_unit_id
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        <?= html_escape($unit->name); ?>
                                        <?= !empty($unit->code)
                                            ? ' (' . html_escape($unit->code) . ')'
                                            : ''; ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="uc-dual-note">
                        <i class="fa fa-info-circle"></i>
                        <span>
                            <?= html_escape(
                                $T(
                                    'အလေးချိန်နှင့် အရေအတွက်ကို သီးခြားလက်ကျန်ထားပါမည်။ ဒုတိယယူနစ်ကို ထုပ်ပိုးမှုအဖြစ် ပြောင်းလဲတွက်ချက်မည်မဟုတ်ပါ။',
                                    'Weight and count are tracked separately. The secondary unit is not converted as a packaging level.'
                                )
                            ); ?>
                        </span>
                    </div>

                </div>
            </div>
        <?php } ?>


        <!-- HELP -->
        <div class="uc-intro">

            <div class="uc-intro-title">
                <i class="fa fa-info-circle"></i>

                <?= html_escape(
                    $T(
                        'ဘယ်လိုဖြည့်ရမလဲ?',
                        'How do I fill this in?'
                    )
                ); ?>
            </div>

            <div class="uc-intro-text">
                <?= html_escape(
                    $T(
                        $is_dual_unit
                            ? 'အခြေခံအလေးချိန်ယူနစ်ကို အရင်ရွေးပါ။ ပြီးရင် အလေးချိန်ယူနစ်များကို အငယ်ဆုံးကနေ အကြီးဆုံးအထိ ထည့်ပါ။ အရေအတွက်ယူနစ်ကို သီးခြားလက်ကျန်ထားပါမည်။'
                            : 'အငယ်ဆုံး ရေတွက်မည့်ယူနစ်ကို အရင်ရွေးပါ။ ပြီးရင် ထုပ်ပိုးမှုကို အငယ်ဆုံးကနေ အကြီးဆုံးအထိ အဆင့်လိုက် ထည့်ပါ။ ကိုယ်တိုင်တွက်စရာမလိုပါဘူး။ စနစ်က အလိုအလျောက်တွက်ပေးပါမယ်။',
                        $is_dual_unit
                            ? 'Choose the primary weight unit first, then add weight units from smallest to largest. The counting unit is tracked separately.'
                            : 'Choose the smallest counting unit first, then add packaging levels from smallest to largest. The system calculates everything automatically.'
                    )
                ); ?>
            </div>

            <div class="uc-example">
                <?= html_escape(
                    $T(
                        $is_dual_unit
                            ? 'ဥပမာ — ၁ ပေါင်မှာ ၁၆ အောင်စ။ “လုံး” အရေအတွက်ကို အလေးချိန်နှင့် သီးခြားထည့်ပါ။ ၁ ပေါင် = ၃ လုံးဟု Conversion မထည့်ရပါ။'
                            : 'ဥပမာ — အငယ်ဆုံးယူနစ် “လုံး” → ၁ ကတ်မှာ ၁၀ လုံး → ၁ ဘူးမှာ ၁၀ ကတ် → ၁ ဖာမှာ ၁၀ ဘူး',
                        $is_dual_unit
                            ? 'Example — 1 pound contains 16 ounces. Enter piece count separately from weight; do not add 1 pound = 3 pieces as a conversion.'
                            : 'Example — Smallest unit: Piece → 1 Card contains 10 Pieces → 1 Box contains 10 Cards → 1 Carton contains 10 Boxes'
                    )
                ); ?>
            </div>

        </div>


        <!-- PACKAGING -->
        <div class="uc-section">

            <div class="uc-section-header">
                <h5 class="uc-section-title">
                    <i class="fa fa-cubes"></i>

                    <?= html_escape(
                        $T(
                            $is_dual_unit
                                ? 'အလေးချိန်ယူနစ် ပြောင်းလဲမှုများ'
                                : 'ထုပ်ပိုးပုံ အဆင့်များ',
                            $is_dual_unit
                                ? 'Weight Unit Conversions'
                                : 'Packaging Levels'
                        )
                    ); ?>
                </h5>

                <button
                    type="button"
                    id="add_conversion_row"
                    class="btn btn-sm uc-add-btn"
                >
                    <i class="fa fa-plus"></i>

                    <?= html_escape(
                        $T(
                            $is_dual_unit
                                ? 'အလေးချိန်ယူနစ် ထည့်ရန်'
                                : 'နောက်ထပ်ထုပ်ပိုးမှု ထည့်ရန်',
                            $is_dual_unit
                                ? 'Add Weight Unit'
                                : 'Add Packaging Level'
                        )
                    ); ?>
                </button>
            </div>


            <!-- COLUMN HEADERS -->
            <div
                class="uc-fields-header"
                id="unit_fields_header"
                style="<?= empty($conversions)
                    ? 'display:none;'
                    : ''; ?>"
            >
                <div class="uc-field-header">
                    <?= html_escape(
                        $T(
                            'ပထမယူနစ်',
                            'First Unit'
                        )
                    ); ?>
                </div>

                <div class="uc-field-header">
                    <?= html_escape(
                        $T(
                            'ဒုတိယယူနစ်',
                            'Second Unit'
                        )
                    ); ?>
                </div>

                <div class="uc-field-header">
                    <?= html_escape(
                        $T(
                            'ပါဝင်သည့်ပမာဏ / အရေအတွက်',
                            'Contained Quantity'
                        )
                    ); ?>
                </div>
            </div>


            <!-- READ ONLY ROWS -->
            <div
                id="conversion_list"
                class="uc-conversion-list"
            >

                <?php if (!empty($conversions)) { ?>

                    <?php
                    $previous_unit_name = $current_base_unit_name;
                    $step = 1;

                    foreach ($conversions as $conversion) {

                        $chain_qty =
                            isset($conversion->chain_qty)
                                ? (float) $conversion->chain_qty
                                : 1;
                    ?>

                        <div class="uc-conversion-row">

                            <div class="uc-step">
                                <?= html_escape(
                                    $T(
                                        'အဆင့် ' . $step,
                                        'Step ' . $step
                                    )
                                ); ?>
                            </div>


                            <div class="uc-sentence">

                                <!-- FIRST UNIT -->
                                <div class="uc-field-box uc-first-unit-box">
                                    <div
                                        class="uc-parent-unit"
                                        data-parent-unit
                                    >
                                        <?= html_escape(
                                            $previous_unit_name
                                        ); ?>
                                    </div>
                                </div>


                                <!-- SECOND UNIT READ ONLY -->
                                <div class="uc-field-box uc-readonly-box">

                                    <div
                                        class="uc-readonly-value"
                                        data-second-unit-text
                                    >
                                        <?= html_escape(
                                            !empty($conversion->unit_name)
                                                ? $conversion->unit_name
                                                : '-'
                                        ); ?>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="unit_id[]"
                                        class="uc-unit-id"
                                        value="<?= (int) $conversion->unit_id; ?>"
                                    >

                                </div>


                                <!-- QUANTITY READ ONLY -->
                                <div class="uc-field-box uc-readonly-box">

                                    <div
                                        class="uc-readonly-value"
                                        data-qty-text
                                    >
                                        <?= html_escape(
                                            (int) round($chain_qty)
                                        ); ?>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="chain_qty[]"
                                        class="uc-chain-qty"
                                        value="<?= html_escape(
                                            (int) round($chain_qty)
                                        ); ?>"
                                    >

                                </div>

                            </div>


                            <!-- PREVIEW -->
                            <div
                                class="uc-preview"
                                data-preview
                            >
                                <i class="fa fa-calculator"></i>
                                <span></span>
                            </div>


                            <!-- ROW ACTIONS -->
                            <div class="uc-row-footer">

                                <button
                                    type="button"
                                    class="btn btn-sm uc-edit-btn edit-conversion-row"
                                >
                                    <i class="fa fa-pencil"></i>

                                    <?= html_escape(
                                        $T(
                                            'ပြင်ရန်',
                                            'Edit'
                                        )
                                    ); ?>
                                </button>


                                <button
                                    type="button"
                                    class="btn btn-sm uc-remove-btn remove-conversion-row"
                                >
                                    <i class="fa fa-trash"></i>

                                    <?= html_escape(
                                        $T(
                                            'ဖျက်ရန်',
                                            'Delete'
                                        )
                                    ); ?>
                                </button>

                            </div>

                        </div>

                    <?php
                        $previous_unit_name =
                            !empty($conversion->unit_name)
                                ? $conversion->unit_name
                                : '-';

                        $step++;
                    } ?>

                <?php } ?>

            </div>


            <!-- EMPTY -->
            <div
                id="conversion_empty"
                class="uc-empty"
                style="<?= !empty($conversions)
                    ? 'display:none;'
                    : ''; ?>"
            >
                <div class="uc-empty-icon">
                    <i class="fa fa-cubes"></i>
                </div>

                <div>
                    <?= html_escape(
                        $T(
                            $is_dual_unit
                                ? 'အလေးချိန်ယူနစ် ပြောင်းလဲမှု မသတ်မှတ်ရသေးပါ။'
                                : 'ထုပ်ပိုးပုံ မသတ်မှတ်ရသေးပါ။',
                            $is_dual_unit
                                ? 'No weight unit conversions have been added yet.'
                                : 'No packaging levels have been added yet.'
                        )
                    ); ?>
                </div>

                <div style="margin-top:5px;font-size:15px;">
                    <?= html_escape(
                        $T(
                            $is_dual_unit
                                ? '“အလေးချိန်ယူနစ် ထည့်ရန်” ကိုနှိပ်ပြီး ပြောင်းလဲမှု ထည့်နိုင်ပါသည်။'
                                : '“နောက်ထပ်ထုပ်ပိုးမှု ထည့်ရန်” ကိုနှိပ်ပြီး ထုပ်ပိုးပုံ ထည့်နိုင်ပါသည်။',
                            $is_dual_unit
                                ? 'Click “Add Weight Unit” to add a conversion.'
                                : 'Click “Add Packaging Level” to add a packaging level.'
                        )
                    ); ?>
                </div>
            </div>


            <!-- FINAL RESULT -->
            <div
                class="uc-result-box"
                id="conversion_result_box"
                style="display:none;"
            >
                <div class="uc-result-title">
                    <i class="fa fa-check-circle"></i>

                    <?= html_escape(
                        $T(
                            $is_dual_unit
                                ? 'Dual Unit သတ်မှတ်ချက်'
                                : 'ထုပ်ပိုးပုံ',
                            $is_dual_unit
                                ? 'Dual Unit Configuration'
                                : 'Packaging'
                        )
                    ); ?>
                </div>

                <div id="conversion_result"></div>
            </div>

        </div>


        <!-- Changes are saved automatically. -->


        <!-- BASE UNIT EDIT MODAL -->
        <div
            class="modal fade"
            id="editBaseUnitModal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div
                class="modal-dialog"
                role="document"
                style="max-width:520px;"
            >
                <div class="modal-content">

                    <div class="modal-header">

                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>


                        <h4 class="modal-title">
                            <i class="fa fa-pencil"></i>

                            <?= html_escape(
                                $T(
                                    $is_dual_unit
                                        ? 'အခြေခံအလေးချိန်ယူနစ် ပြင်ရန်'
                                        : 'အငယ်ဆုံး ရေတွက်မည့်ယူနစ် ပြင်ရန်',
                                    $is_dual_unit
                                        ? 'Edit Primary Weight Unit'
                                        : 'Edit Smallest Counting Unit'
                                )
                            ); ?>
                        </h4>

                    </div>


                    <div class="modal-body">

                        <div class="uc-modal-form-group">

                            <label
                                for="modal_base_unit"
                                class="uc-modal-label"
                            >
                                <?= html_escape(
                                    $T(
                                        $is_dual_unit
                                            ? 'အခြေခံအလေးချိန်ယူနစ်'
                                            : 'အငယ်ဆုံး ရေတွက်မည့်ယူနစ်',
                                        $is_dual_unit
                                            ? 'Primary Weight Unit'
                                            : 'Smallest Counting Unit'
                                    )
                                ); ?>

                                <span class="uc-required">*</span>
                            </label>


                            <div class="uc-modal-input-box">

                                <select
                                    id="modal_base_unit"
                                    class="form-control"
                                    style="width:100%;"
                                >
                                    <option value="">
                                        <?= html_escape(
                                            $T(
                                                'ယူနစ်ရွေးချယ်ရန်',
                                                'Select Unit'
                                            )
                                        ); ?>
                                    </option>

                                    <?php foreach ($units as $unit) { ?>

                                        <option
                                            value="<?= (int) $unit->id; ?>"
                                            data-name="<?= html_escape($unit->name); ?>"
                                            <?= $is_dual_unit &&
                                                (int) $unit->id === $current_secondary_unit_id
                                                    ? 'disabled'
                                                    : ''; ?>
                                        >
                                            <?= html_escape($unit->name); ?>

                                            <?php if (!empty($unit->code)) { ?>
                                                (<?= html_escape($unit->code); ?>)
                                            <?php } ?>
                                        </option>

                                    <?php } ?>
                                </select>

                            </div>

                        </div>


                        <div
                            class="uc-modal-preview"
                            style="display:block;"
                        >
                            <i class="fa fa-info-circle"></i>

                            <span>
                                <?= html_escape(
                                    $T(
                                        $is_dual_unit
                                            ? 'သီးခြားရေတွက်မည့် ဒုတိယယူနစ်နှင့် ပြောင်းလဲမှုတွင် အသုံးပြုပြီးသားယူနစ်ကို အခြေခံအလေးချိန်ယူနစ်အဖြစ် မရွေးနိုင်ပါ။'
                                            : 'ထုပ်ပိုးအဆင့်တွင် အသုံးပြုထားပြီးသား ယူနစ်ကို အငယ်ဆုံးယူနစ်အဖြစ် မရွေးနိုင်ပါ။',
                                        $is_dual_unit
                                            ? 'The independent counting unit and a unit already used in a conversion cannot be selected as the primary weight unit.'
                                            : 'A unit already used in a packaging level cannot be selected as the smallest unit.'
                                    )
                                ); ?>
                            </span>
                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-default"
                            data-dismiss="modal"
                        >
                            <i class="fa fa-times"></i>

                            <?= html_escape(
                                $T(
                                    'မလုပ်တော့ပါ',
                                    'Cancel'
                                )
                            ); ?>
                        </button>


                        <button
                            type="button"
                            id="confirm_base_unit_edit"
                            class="btn uc-save-btn"
                        >
                            <i class="fa fa-save"></i>

                            <?= html_escape(
                                $T(
                                    'ပြင်ဆင်မည်',
                                    'Update'
                                )
                            ); ?>
                        </button>

                    </div>

                </div>
            </div>
        </div>


        <!-- ADD / EDIT MODAL -->
        <div
            class="modal fade"
            id="addPackagingModal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div
                class="modal-dialog"
                role="document"
            >
                <div class="modal-content">

                    <!-- HEADER -->
                    <div class="modal-header">

                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>

                        <h4 class="modal-title">
                            <i class="fa fa-plus-circle"></i>

                            <?= html_escape(
                                $T(
                                    $is_dual_unit
                                        ? 'အလေးချိန်ယူနစ် ထည့်ရန်'
                                        : 'နောက်ထပ်ထုပ်ပိုးမှု ထည့်ရန်',
                                    $is_dual_unit
                                        ? 'Add Weight Unit'
                                        : 'Add Packaging Level'
                                )
                            ); ?>
                        </h4>

                    </div>


                    <!-- BODY -->
                    <div class="modal-body">

                        <!-- FIRST + SECOND UNIT SAME ROW -->
                        <div class="uc-modal-unit-row">

                            <!-- FIRST UNIT -->
                            <div class="uc-modal-form-group">

                                <label class="uc-modal-label">
                                    <?= html_escape(
                                        $T(
                                            'ပထမယူနစ်',
                                            'First Unit'
                                        )
                                    ); ?>
                                </label>

                                <div
                                    class="uc-modal-first-unit"
                                    id="modal_first_unit"
                                    data-unit-id=""
                                >
                                    -
                                </div>

                            </div>


                            <!-- SECOND UNIT -->
                            <div class="uc-modal-form-group">

                                <label
                                    for="modal_second_unit"
                                    class="uc-modal-label"
                                >
                                    <?= html_escape(
                                        $T(
                                            'ဒုတိယယူနစ်',
                                            'Second Unit'
                                        )
                                    ); ?>

                                    <span class="uc-required">*</span>
                                </label>

                                <div class="uc-modal-input-box">

                                    <select
                                        id="modal_second_unit"
                                        class="form-control"
                                        style="width:100%;"
                                    >
                                        <option value="">
                                            <?= html_escape(
                                                $T(
                                                    'ဒုတိယယူနစ် ရွေးပါ',
                                                    'Select Second Unit'
                                                )
                                            ); ?>
                                        </option>
                                    </select>

                                </div>

                            </div>

                        </div>


                        <!-- QUANTITY FULL ROW -->
                        <div class="uc-modal-form-group">

                            <label
                                for="modal_chain_qty"
                                class="uc-modal-label"
                            >
                                <?= html_escape(
                                    $T(
                                        'ပါဝင်သည့်ပမာဏ / အရေအတွက်',
                                        'Contained Quantity'
                                    )
                                ); ?>

                                <span class="uc-required">*</span>
                            </label>

                            <div class="uc-modal-input-box">

                                <input
                                    type="number"
                                    id="modal_chain_qty"
                                    class="form-control uc-modal-qty"
                                    min="1"
                                    step="1"
                                    placeholder="10"
                                    inputmode="numeric"
                                >

                            </div>

                        </div>


                        <!-- PREVIEW -->
                        <div
                            class="uc-modal-preview"
                            id="modal_conversion_preview"
                        >
                            <i class="fa fa-calculator"></i>
                            <span></span>
                        </div>

                    </div>


                    <!-- FOOTER -->
                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-default"
                            data-dismiss="modal"
                        >
                            <i class="fa fa-times"></i>

                            <?= html_escape(
                                $T(
                                    'မလုပ်တော့ပါ',
                                    'Cancel'
                                )
                            ); ?>
                        </button>


                        <button
                            type="button"
                            id="confirm_add_packaging"
                            class="btn uc-save-btn"
                        >
                            <i class="fa fa-plus"></i>

                            <?= html_escape(
                                $T(
                                    'ထည့်မည်',
                                    'Add'
                                )
                            ); ?>
                        </button>

                    </div>

                </div>
            </div>
        </div>


        <!-- AUTO SAVE STATUS -->
        <div
            id="uc_autosave_overlay"
            class="uc-autosave-overlay"
        >
            <div class="uc-autosave-box">
                <i class="fa fa-spinner fa-spin"></i>
                <span>
                    <?= html_escape(
                        $T(
                            'အလိုအလျောက် သိမ်းနေသည်...',
                            'Saving automatically...'
                        )
                    ); ?>
                </span>
            </div>
        </div>


        <?= form_close(); ?>

    </div>

</div>
</div>
</section>


<script>
(function ($) {

    'use strict';

    // =====================================================
    // DATA
    // =====================================================

    var allUnits =
        <?= json_encode(
            $units_for_js,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

    var editingRow = null;

    var savedBaseUnitId =
        String(<?= (int) $current_base_unit_id; ?>);

    var isDualUnit =
        <?= $is_dual_unit ? 'true' : 'false'; ?>;

    var secondaryUnitId =
        String(<?= (int) $current_secondary_unit_id; ?>);

    var secondaryUnitName =
        <?= json_encode(
            (string) $current_secondary_unit_name,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;

    $('#secondary_unit_id').on('change', function () {

        secondaryUnitId = String($(this).val() || '');
        secondaryUnitName = $(this).find('option:selected').text().trim();

        if (initializingPage || !secondaryUnitId) {
            return;
        }

        refreshAllRows();
        autoSaveForm();
    });

    var autoSaving =
        false;

    /*
     * Prevent Select2 initialization on page load from being treated
     * as a real user change. Without this guard, the page can enter
     * an auto-save -> redirect -> auto-save loop.
     */
    var initializingPage =
        true;

    var selectPackageText =
        <?= json_encode(
            $T(
                'ဒုတိယယူနစ် ရွေးပါ',
                'Select Second Unit'
            ),
            JSON_UNESCAPED_UNICODE
        ); ?>;

    var selectBaseFirstText =
        <?= json_encode(
            $T(
                $is_dual_unit
                    ? 'အခြေခံအလေးချိန်ယူနစ်ကို အရင်ရွေးပါ။'
                    : 'အငယ်ဆုံး ရေတွက်မည့်ယူနစ်ကို အရင်ရွေးပါ။',
                $is_dual_unit
                    ? 'Please select the primary weight unit first.'
                    : 'Please select the smallest counting unit first.'
            ),
            JSON_UNESCAPED_UNICODE
        ); ?>;

    var duplicateText =
        <?= json_encode(
            $T(
                'ဒီယူနစ်ကို ထည့်ပြီးသားဖြစ်ပါသည်။ အခြားယူနစ်တစ်ခု ရွေးပါ။',
                'This unit has already been added. Please select another unit.'
            ),
            JSON_UNESCAPED_UNICODE
        ); ?>;

    var fillAllText =
        <?= json_encode(
            $T(
                $is_dual_unit
                    ? 'အလေးချိန်ယူနစ် ပြောင်းလဲမှုတိုင်းကို ပြည့်စုံအောင် ဖြည့်ပါ။ ဒုတိယအရေအတွက်ယူနစ်ကို မရွေးရပါ။'
                    : 'ထုပ်ပိုးပုံအဆင့်တိုင်းကို ပြည့်စုံအောင် ဖြည့်ပါ။',
                $is_dual_unit
                    ? 'Complete every weight conversion. The secondary counting unit cannot be selected here.'
                    : 'Please complete every packaging level.'
            ),
            JSON_UNESCAPED_UNICODE
        ); ?>;

    var calculatedPrefix =
        <?= json_encode(
            $T(
                'အလိုအလျောက်တွက်ချက်မှု — ',
                'Calculated — '
            ),
            JSON_UNESCAPED_UNICODE
        ); ?>;


    // =====================================================
    // HELPERS
    // =====================================================

    function escapeHtml(value) {
        return $('<div>')
            .text(
                value === null ||
                value === undefined
                    ? ''
                    : value
            )
            .html();
    }


    function numberValue(value) {
        var n = parseFloat(value);

        return isFinite(n)
            ? n
            : 0;
    }


    function formatNumber(value) {
        var n = numberValue(value);

        if (
            Math.abs(
                n - Math.round(n)
            ) < 0.000001
        ) {
            return Math.round(n)
                .toLocaleString('en-US');
        }

        return n.toLocaleString(
            'en-US',
            {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }
        );
    }


    function unitNameById(id) {
        id = String(id || '');

        var name = '';

        $.each(
            allUnits,
            function (_, unit) {
                if (
                    String(unit.id) === id
                ) {
                    name = unit.name;
                    return false;
                }
            }
        );

        return name || '-';
    }


    function baseUnitId() {
        return String(
            $('#base_unit_id').val() || ''
        );
    }


    function baseUnitName() {

        var id =
            baseUnitId();

        if (!id) {
            return <?= json_encode(
                $T(
                    'ယူနစ်မရွေးရသေးပါ',
                    'No unit selected'
                ),
                JSON_UNESCAPED_UNICODE
            ); ?>;
        }

        return unitNameById(
            id
        );
    }


    function showAlert(message) {
        if (window.bootbox) {
            bootbox.alert(message);
        } else {
            alert(message);
        }
    }


    // =====================================================
    // AUTO SAVE
    // =====================================================

    function showAutoSaveStatus() {
        $('#uc_autosave_overlay')
            .css('display', 'flex');
    }


    function autoSaveForm() {

        if (autoSaving) {
            return false;
        }

        if (!baseUnitId()) {
            showAlert(
                selectBaseFirstText
            );
            return false;
        }

        if (!validateRows()) {
            return false;
        }

        autoSaving = true;

        showAutoSaveStatus();

        $('#add_conversion_row, .uc-edit-btn, .uc-remove-btn, #confirm_add_packaging')
            .prop(
                'disabled',
                true
            );

        var form =
            document.getElementById(
                'unit_conversion_form'
            );

        if (!form) {
            autoSaving = false;
            $('#uc_autosave_overlay').hide();
            return false;
        }

        /*
         * Native submit is intentional so it does not
         * re-enter the jQuery submit handler below.
         */
        form.submit();

        return true;
    }


    // =====================================================
    // SELECT2
    // =====================================================

    function destroySelect2($select) {

        if (
            !$.fn.select2 ||
            !$select ||
            !$select.length
        ) {
            return;
        }

        /* Select2 တကယ်တပ်ထားသော element ကိုသာ destroy ခေါ်မည်။ */
        var hasSelect2 =
            !!$select.data('select2') ||
            $select.hasClass('select2-hidden-accessible') ||
            $select.hasClass('select2-offscreen');

        if (hasSelect2) {
            try {
                $select.select2('destroy');
            } catch (ignored) {}
        }

        $select
            .siblings('.select2-container')
            .remove();

        $select
            .removeClass(
                'select2-hidden-accessible select2-offscreen'
            )
            .removeAttr(
                'data-select2-id aria-hidden tabindex'
            );

        $select
            .find('option')
            .removeAttr('data-select2-id');
    }


    function initSelect2($select) {

        if (
            !$.fn.select2 ||
            !$select ||
            !$select.length
        ) {
            return;
        }

        var selectedValue =
            $select.val();

        destroySelect2($select);

        $select.css('width', '100%');

        var select2Options = {
            width: '100%',
            dropdownAutoWidth: true,
            minimumResultsForSearch: 0
        };

        /* Select2 v4 တွင် dropdown ကို သက်ဆိုင်ရာ modal အတွင်းထားမည်။ */
        var $parentModal = $select.closest('.modal');
        if ($parentModal.length) {
            select2Options.dropdownParent = $parentModal;
        }

        try {
            $select.select2(select2Options);
        } catch (error) {
            try {
                $select.select2();
            } catch (ignored) {}
        }

        if (
            selectedValue !== null &&
            selectedValue !== undefined
        ) {

            $select.val(
                String(selectedValue)
            );

            try {
                $select.trigger(
                    'change.select2'
                );
            } catch (ignored) {}

            try {
                $select.select2(
                    'val',
                    String(selectedValue)
                );
            } catch (ignored) {}
        }

        $select
            .next('.select2-container')
            .css('width', '100%');

        /* Dropdown ဖွင့်သည်နှင့် search input ကို စာရိုက်နိုင်အောင် focus ပေးမည်။ */
        $select
            .off('select2-open.ucSearch select2:open.ucSearch')
            .on(
                'select2-open.ucSearch select2:open.ucSearch',
                function () {
                    window.setTimeout(function () {
                        $('.select2-drop-active .select2-input:visible, ' +
                          '.select2-container--open .select2-search__field:visible')
                            .last()
                            .trigger('focus');
                    }, 0);
                }
            );
    }


    function initBaseSelect2() {
        return;
    }

    /*
     * Bootstrap 3 modal focus lock က body အောက်တွင်ဖွင့်သော Select2 v3 search
     * input ကို focus မပေးသဖြင့် keyboard စာရိုက်မရခြင်းကို ဖြေရှင်းမည်။
     */
    $('#addPackagingModal, #editBaseUnitModal')
        .on('shown.bs.modal', function () {
            $(document)
                .off('focusin.bs.modal')
                .off('focusin.modal');
        });


    // =====================================================
    // USED UNITS
    // =====================================================

    function getUsedUnits(exceptRow) {

        var used = {};

        $('.uc-conversion-row')
            .each(function () {

                var $row = $(this);

                if (
                    exceptRow &&
                    exceptRow.length &&
                    $row[0] === exceptRow[0]
                ) {
                    return;
                }

                var id =
                    String(
                        $row
                            .find('.uc-unit-id')
                            .val() || ''
                    );

                if (id) {
                    used[id] = true;
                }
            });

        return used;
    }


    // =====================================================
    // BUILD MODAL UNIT OPTIONS
    // =====================================================

    function buildModalUnitOptions(
        currentUnitId,
        exceptRow
    ) {

        var baseId =
            baseUnitId();

        var used =
            getUsedUnits(
                exceptRow
            );

        var html =
            '<option value="">' +
                escapeHtml(
                    selectPackageText
                ) +
            '</option>';

        $.each(
            allUnits,
            function (_, unit) {

                var unitId =
                    String(unit.id);

                // Base unit cannot be second unit
                if (
                    unitId === baseId
                ) {
                    return;
                }

                // A dual product's count unit is independent, not packaging.
                if (
                    isDualUnit &&
                    secondaryUnitId &&
                    unitId === secondaryUnitId
                ) {
                    return;
                }

                // Already used in another row
                if (
                    used[unitId]
                ) {
                    return;
                }

                var label =
                    unit.name;

                if (unit.code) {
                    label +=
                        ' (' +
                        unit.code +
                        ')';
                }

                html +=
                    '<option ' +
                        'value="' +
                        escapeHtml(unit.id) +
                        '" ' +
                        'data-name="' +
                        escapeHtml(unit.name) +
                        '"' +
                        (
                            String(currentUnitId || '') === unitId
                                ? ' selected'
                                : ''
                        ) +
                    '>' +
                        escapeHtml(label) +
                    '</option>';
            }
        );

        return html;
    }


    // =====================================================
    // GET FIRST UNIT FOR NEW ROW
    // =====================================================

    function getNextFirstUnit() {

        var result = {
            id: baseUnitId(),
            name: baseUnitName()
        };

        $('.uc-conversion-row')
            .each(function () {

                var unitId =
                    String(
                        $(this)
                            .find('.uc-unit-id')
                            .val() || ''
                    );

                if (unitId) {
                    result.id = unitId;
                    result.name =
                        unitNameById(
                            unitId
                        );
                }
            });

        return result;
    }


    // =====================================================
    // MODAL MODE
    // =====================================================

    function setModalAddMode() {

        $('#addPackagingModal .modal-title')
            .html(
                '<i class="fa fa-plus-circle"></i> ' +
                <?= json_encode(
                    $T(
                        $is_dual_unit
                            ? 'အလေးချိန်ယူနစ် ထည့်ရန်'
                            : 'နောက်ထပ်ထုပ်ပိုးမှု ထည့်ရန်',
                        $is_dual_unit
                            ? 'Add Weight Unit'
                            : 'Add Packaging Level'
                    ),
                    JSON_UNESCAPED_UNICODE
                ); ?>
            );

        $('#confirm_add_packaging')
            .html(
                '<i class="fa fa-plus"></i> ' +
                <?= json_encode(
                    $T(
                        'ထည့်မည်',
                        'Add'
                    ),
                    JSON_UNESCAPED_UNICODE
                ); ?>
            );
    }


    function setModalEditMode() {

        $('#addPackagingModal .modal-title')
            .html(
                '<i class="fa fa-pencil"></i> ' +
                <?= json_encode(
                    $T(
                        $is_dual_unit
                            ? 'အလေးချိန်ယူနစ် ပြင်ရန်'
                            : 'ထုပ်ပိုးမှု ပြင်ရန်',
                        $is_dual_unit
                            ? 'Edit Weight Unit'
                            : 'Edit Packaging Level'
                    ),
                    JSON_UNESCAPED_UNICODE
                ); ?>
            );

        $('#confirm_add_packaging')
            .html(
                '<i class="fa fa-save"></i> ' +
                <?= json_encode(
                    $T(
                        'ပြင်ဆင်မည်',
                        'Update'
                    ),
                    JSON_UNESCAPED_UNICODE
                ); ?>
            );
    }


    // =====================================================
    // OPEN ADD MODAL
    // =====================================================

    function openAddPackagingModal() {

        if (!baseUnitId()) {
            showAlert(
                selectBaseFirstText
            );

            return false;
        }

        editingRow = null;

        setModalAddMode();

        var firstUnit =
            getNextFirstUnit();

        $('#modal_first_unit')
            .text(firstUnit.name)
            .attr(
                'data-unit-id',
                firstUnit.id
            );

        var $secondUnit =
            $('#modal_second_unit');

        destroySelect2(
            $secondUnit
        );

        $secondUnit
            .html(
                buildModalUnitOptions(
                    '',
                    null
                )
            )
            .val('');

        $('#modal_chain_qty')
            .val('');

        $('#modal_conversion_preview')
            .hide()
            .find('span')
            .text('');

        $('#addPackagingModal')
            .modal('show');

        window.setTimeout(
            function () {
                initSelect2(
                    $secondUnit
                );
            },
            150
        );

        return true;
    }


    // =====================================================
    // OPEN EDIT MODAL
    // =====================================================

    function openEditPackagingModal($row) {

        if (
            !$row ||
            !$row.length
        ) {
            return false;
        }

        editingRow = $row;

        setModalEditMode();

        var firstUnitName =
            $.trim(
                $row
                    .find('[data-parent-unit]')
                    .text()
            );

        var currentUnitId =
            String(
                $row
                    .find('.uc-unit-id')
                    .val() || ''
            );

        var currentQty =
            $row
                .find('.uc-chain-qty')
                .val();

        $('#modal_first_unit')
            .text(firstUnitName);

        var $secondUnit =
            $('#modal_second_unit');

        destroySelect2(
            $secondUnit
        );

        $secondUnit
            .html(
                buildModalUnitOptions(
                    currentUnitId,
                    $row
                )
            )
            .val(
                currentUnitId
            );

        $('#modal_chain_qty')
            .val(
                currentQty
            );

        $('#addPackagingModal')
            .modal('show');

        window.setTimeout(
            function () {

                initSelect2(
                    $secondUnit
                );

                $secondUnit
                    .val(
                        currentUnitId
                    );

                try {
                    $secondUnit.trigger(
                        'change.select2'
                    );
                } catch (ignored) {}

                try {
                    $secondUnit.select2(
                        'val',
                        currentUnitId
                    );
                } catch (ignored) {}

                refreshModalPreview();
            },
            150
        );

        return true;
    }


    // =====================================================
    // CREATE READ-ONLY ROW
    // =====================================================

    function addRowFromModal(
        selectedUnitId,
        qty
    ) {

        var selectedUnitName =
            unitNameById(
                selectedUnitId
            );

        var html =

            '<div class="uc-conversion-row">' +

                '<div class="uc-step"></div>' +

                '<div class="uc-sentence">' +

                    // FIRST UNIT
                    '<div class="uc-field-box uc-first-unit-box">' +
                        '<div ' +
                            'class="uc-parent-unit" ' +
                            'data-parent-unit>' +
                        '</div>' +
                    '</div>' +

                    // SECOND UNIT READ ONLY
                    '<div class="uc-field-box uc-readonly-box">' +
                        '<div ' +
                            'class="uc-readonly-value" ' +
                            'data-second-unit-text>' +
                            escapeHtml(
                                selectedUnitName
                            ) +
                        '</div>' +

                        '<input ' +
                            'type="hidden" ' +
                            'name="unit_id[]" ' +
                            'class="uc-unit-id" ' +
                            'value="' +
                            escapeHtml(
                                selectedUnitId
                            ) +
                            '">' +
                    '</div>' +

                    // QTY READ ONLY
                    '<div class="uc-field-box uc-readonly-box">' +
                        '<div ' +
                            'class="uc-readonly-value" ' +
                            'data-qty-text>' +
                            escapeHtml(
                                formatNumber(qty)
                            ) +
                        '</div>' +

                        '<input ' +
                            'type="hidden" ' +
                            'name="chain_qty[]" ' +
                            'class="uc-chain-qty" ' +
                            'value="' +
                            escapeHtml(qty) +
                            '">' +
                    '</div>' +

                '</div>' +

                '<div ' +
                    'class="uc-preview" ' +
                    'data-preview>' +
                    '<i class="fa fa-calculator"></i>' +
                    '<span></span>' +
                '</div>' +

                '<div class="uc-row-footer">' +

                    '<button ' +
                        'type="button" ' +
                        'class="btn btn-sm uc-edit-btn edit-conversion-row">' +
                        '<i class="fa fa-pencil"></i> ' +
                        <?= json_encode(
                            $T(
                                'ပြင်ရန်',
                                'Edit'
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +
                    '</button>' +

                    '<button ' +
                        'type="button" ' +
                        'class="btn btn-sm uc-remove-btn remove-conversion-row">' +
                        '<i class="fa fa-trash"></i> ' +
                        <?= json_encode(
                            $T(
                                'ဖျက်ရန်',
                                'Delete'
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +
                    '</button>' +

                '</div>' +

            '</div>';

        $('#conversion_list')
            .append(html);

        refreshAllRows();

        return true;
    }


    // =====================================================
    // REFRESH ROWS / CALCULATION
    // =====================================================

    function refreshAllRows() {

        var baseName =
            baseUnitName();

        var previousName =
            baseName;

        var runningMultiplier =
            1;

        var resultHtml =
            '';

        var resultParts =
            [];

        var productName =
            <?= json_encode(
                (string) $product->name,
                JSON_UNESCAPED_UNICODE
            ); ?>;

        var hasResult =
            false;

        var rowCount =
            $('.uc-conversion-row')
                .length;

        $('#unit_fields_header')
            .toggle(
                rowCount > 0
            );

        $('#conversion_empty')
            .toggle(
                rowCount === 0
            );

        $('.uc-conversion-row')
            .each(function (index) {

                var $row =
                    $(this);

                var step =
                    index + 1;

                var selectedUnitId =
                    String(
                        $row
                            .find('.uc-unit-id')
                            .val() || ''
                    );

                var selectedUnitName =
                    unitNameById(
                        selectedUnitId
                    );

                var qty =
                    numberValue(
                        $row
                            .find('.uc-chain-qty')
                            .val()
                    );

                // Step
                $row
                    .find('.uc-step')
                    .text(
                        <?= json_encode(
                            $T(
                                'အဆင့် ',
                                'Step '
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +
                        step
                    );

                // First unit
                $row
                    .find('[data-parent-unit]')
                    .text(
                        previousName
                    );

                // Sync visible read-only data
                $row
                    .find('[data-second-unit-text]')
                    .text(
                        selectedUnitName
                    );

                $row
                    .find('[data-qty-text]')
                    .text(
                        formatNumber(qty)
                    );

                if (
                    selectedUnitId &&
                    qty > 0
                ) {

                    var directRelation =

                        <?= json_encode(
                            $T(
                                '၁ ',
                                '1 '
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +

                        selectedUnitName +

                        <?= json_encode(
                            $T(
                                'မှာ ',
                                ' contains '
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +

                        formatNumber(qty) +

                        ' ' +

                        previousName +

                        <?= json_encode(
                            $T(
                                ' ပါတယ်',
                                ''
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?>;

                    runningMultiplier =
                        runningMultiplier *
                        qty;

                    var finalCalculation =

                        <?= json_encode(
                            $T(
                                '၁ ',
                                '1 '
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +

                        selectedUnitName +

                        ' = ' +

                        formatNumber(
                            runningMultiplier
                        ) +

                        ' ' +

                        baseName;

                    $row
                        .find('[data-preview] span')
                        .text(
                            directRelation +
                            '  •  ' +
                            calculatedPrefix +
                            finalCalculation
                        );

                    var summaryRelation =

                        <?= json_encode(
                            $T(
                                '၁ ',
                                '1 '
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +

                        selectedUnitName +

                        <?= json_encode(
                            $T(
                                'မှာ ',
                                ' contains '
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +

                        formatNumber(qty) +

                        ' ' +

                        previousName;

                    resultParts.push(
                        summaryRelation
                    );

                    hasResult = true;

                    previousName =
                        selectedUnitName;
                }
            });

        if (resultParts.length) {
            resultHtml =
                '<div class="uc-result-line">' +
                    '<i class="fa fa-check"></i>' +
                    '<strong>' + escapeHtml(productName) + '</strong>' +
                    ' - ' +
                    resultParts
                        .map(escapeHtml)
                        .join(' &rarr; ') +
                '</div>';
        }

        if (isDualUnit) {
            resultHtml +=
                '<div class="uc-result-line">' +
                    '<i class="fa fa-balance-scale"></i>' +
                    '<strong>' +
                        escapeHtml(
                            <?= json_encode(
                                $T(
                                    'Dual Unit — ',
                                    'Dual Unit — '
                                ),
                                JSON_UNESCAPED_UNICODE
                            ); ?>
                        ) +
                    '</strong>' +
                    escapeHtml(
                        <?= json_encode(
                            $T(
                                'အလေးချိန် ',
                                'Weight '
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +
                        baseName
                    ) +
                    ' &nbsp;|&nbsp; ' +
                    escapeHtml(
                        <?= json_encode(
                            $T(
                                'အရေအတွက် ',
                                'Count '
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?> +
                        (
                            secondaryUnitName ||
                            <?= json_encode(
                                $T(
                                    'မရွေးရသေးပါ',
                                    'not selected'
                                ),
                                JSON_UNESCAPED_UNICODE
                            ); ?>
                        )
                    ) +
                    <?= json_encode(
                        $T(
                            ' (သီးခြားတွက်ချက်မည်)',
                            ' (tracked independently)'
                        ),
                        JSON_UNESCAPED_UNICODE
                    ); ?> +
                '</div>';

            hasResult = true;
        }

        $('#conversion_result')
            .html(
                resultHtml
            );

        $('#conversion_result_box')
            .toggle(
                hasResult
            );
    }


    // =====================================================
    // MODAL PREVIEW
    // =====================================================

    function refreshModalPreview() {

        var firstUnitName =
            $.trim(
                $('#modal_first_unit')
                    .text()
            );

        var secondUnitId =
            String(
                $('#modal_second_unit')
                    .val() || ''
            );

        var secondUnitName =
            unitNameById(
                secondUnitId
            );

        var qty =
            numberValue(
                $('#modal_chain_qty')
                    .val()
            );

        if (
            secondUnitId &&
            qty >= 1
        ) {

            var text =

                <?= json_encode(
                    $T(
                        '၁ ',
                        '1 '
                    ),
                    JSON_UNESCAPED_UNICODE
                ); ?> +

                secondUnitName +

                <?= json_encode(
                    $T(
                        'မှာ ',
                        ' contains '
                    ),
                    JSON_UNESCAPED_UNICODE
                ); ?> +

                formatNumber(qty) +

                ' ' +

                firstUnitName +

                <?= json_encode(
                    $T(
                        ' ပါတယ်',
                        ''
                    ),
                    JSON_UNESCAPED_UNICODE
                ); ?>;

            $('#modal_conversion_preview')
                .show()
                .find('span')
                .text(text);

        } else {

            $('#modal_conversion_preview')
                .hide()
                .find('span')
                .text('');
        }
    }


    // =====================================================
    // VALIDATE ROWS
    // =====================================================

    function validateRows() {

        var used = {};
        var baseId = baseUnitId();
        var valid = true;

        $('.uc-conversion-row')
            .each(function () {

                var $row = $(this);

                var unitId =
                    String(
                        $row
                            .find('.uc-unit-id')
                            .val() || ''
                    );

                var qty =
                    numberValue(
                        $row
                            .find('.uc-chain-qty')
                            .val()
                    );

                $row.css(
                    'border-color',
                    ''
                );

                if (
                    !unitId ||
                    unitId === baseId ||
                    (
                        isDualUnit &&
                        secondaryUnitId &&
                        unitId === secondaryUnitId
                    ) ||
                    qty < 1 ||
                    Math.floor(qty) !== qty ||
                    used[unitId]
                ) {

                    valid = false;

                    $row.css(
                        'border-color',
                        '#dc2626'
                    );

                    return false;
                }

                used[unitId] = true;
            });

        if (!valid) {
            showAlert(
                fillAllText
            );
        }

        return valid;
    }


    // =====================================================
    // ADD BUTTON
    // =====================================================

    $('#add_conversion_row')
        .on(
            'click',
            function () {
                openAddPackagingModal();
                return false;
            }
        );


    // =====================================================
    // EDIT BUTTON
    // =====================================================

    $(document)
        .on(
            'click',
            '.edit-conversion-row',
            function () {

                openEditPackagingModal(
                    $(this)
                        .closest(
                            '.uc-conversion-row'
                        )
                );

                return false;
            }
        );


    // =====================================================
    // REMOVE BUTTON -> DELETE + AUTO SAVE
    // =====================================================

    $(document)
        .on(
            'click',
            '.remove-conversion-row',
            function () {

                var $row =
                    $(this)
                        .closest(
                            '.uc-conversion-row'
                        );


                var removeRow =
                    function () {

                        if (
                            editingRow &&
                            editingRow.length &&
                            $row[0] === editingRow[0]
                        ) {
                            editingRow = null;
                        }

                        $row.remove();

                        refreshAllRows();

                        autoSaveForm();
                    };


                var confirmText =
                    <?= json_encode(
                        $T(
                            'ဒီထုပ်ပိုးအဆင့်ကို ဖျက်မှာ သေချာပါသလား?',
                            'Are you sure you want to delete this packaging level?'
                        ),
                        JSON_UNESCAPED_UNICODE
                    ); ?>;


                if (
                    window.bootbox &&
                    typeof bootbox.confirm === 'function'
                ) {

                    bootbox.confirm(
                        confirmText,
                        function (result) {
                            if (result) {
                                removeRow();
                            }
                        }
                    );

                } else {

                    if (
                        window.confirm(
                            confirmText
                        )
                    ) {
                        removeRow();
                    }
                }

                return false;
            }
        );


    // =====================================================
    // MODAL LIVE PREVIEW
    // =====================================================

    $(document)
        .on(
            'change input',
            '#modal_second_unit, #modal_chain_qty',
            function () {
                refreshModalPreview();
            }
        );


    // =====================================================
    // CONFIRM ADD / EDIT
    // =====================================================

    $('#confirm_add_packaging')
        .on(
            'click',
            function () {

                var secondUnitId =
                    String(
                        $('#modal_second_unit')
                            .val() || ''
                    );

                var qty =
                    numberValue(
                        $('#modal_chain_qty')
                            .val()
                    );

                if (!secondUnitId) {

                    showAlert(
                        <?= json_encode(
                            $T(
                                'ဒုတိယယူနစ်ကို ရွေးပါ။',
                                'Please select the second unit.'
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?>
                    );

                    return false;
                }

                if (
                    qty < 1 ||
                    Math.floor(qty) !== qty
                ) {

                    showAlert(
                        <?= json_encode(
                            $T(
                                'ပါဝင်သည့်အရေအတွက်ကို ၁ သို့မဟုတ် ၁ ထက်ကြီးသော ကိန်းပြည့်ဖြင့် ဖြည့်ပါ။',
                                'Enter a whole quantity of 1 or more.'
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?>
                    );

                    return false;
                }

                var used =
                    getUsedUnits(
                        editingRow
                    );

                if (
                    used[secondUnitId]
                ) {

                    showAlert(
                        duplicateText
                    );

                    return false;
                }

                if (
                    secondUnitId ===
                    baseUnitId()
                ) {

                    showAlert(
                        <?= json_encode(
                            $T(
                                $is_dual_unit
                                    ? 'အခြေခံအလေးချိန်ယူနစ်ကို အကြီးယူနစ်အဖြစ် ထပ်မရွေးရပါ။'
                                    : 'အငယ်ဆုံး ရေတွက်မည့်ယူနစ်ကို ဒုတိယယူနစ်အဖြစ် မရွေးရပါ။',
                                $is_dual_unit
                                    ? 'The primary weight unit cannot also be selected as the larger unit.'
                                    : 'The smallest counting unit cannot be selected as the second unit.'
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?>
                    );

                    return false;
                }

                if (
                    isDualUnit &&
                    secondaryUnitId &&
                    secondUnitId === secondaryUnitId
                ) {

                    showAlert(
                        <?= json_encode(
                            $T(
                                'ဒုတိယအရေအတွက်ယူနစ်ကို အလေးချိန်ပြောင်းလဲမှုတွင် ထည့်၍မရပါ။ အရေအတွက်ကို သီးခြားတွက်ချက်ပါမည်။',
                                'The secondary counting unit cannot be added as a weight conversion because it is tracked independently.'
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?>
                    );

                    return false;
                }

                // EDIT
                if (
                    editingRow &&
                    editingRow.length
                ) {

                    var secondUnitName =
                        unitNameById(
                            secondUnitId
                        );

                    editingRow
                        .find('.uc-unit-id')
                        .val(
                            secondUnitId
                        );

                    editingRow
                        .find('[data-second-unit-text]')
                        .text(
                            secondUnitName
                        );

                    editingRow
                        .find('.uc-chain-qty')
                        .val(
                            qty
                        );

                    editingRow
                        .find('[data-qty-text]')
                        .text(
                            formatNumber(qty)
                        );

                    editingRow = null;

                    refreshAllRows();

                    $('#addPackagingModal')
                        .modal('hide');

                    autoSaveForm();

                    return false;
                }

                // ADD
                addRowFromModal(
                    secondUnitId,
                    qty
                );

                $('#addPackagingModal')
                    .modal('hide');

                autoSaveForm();

                return false;
            }
        );


    // =====================================================
    // BASE UNIT EDIT POPUP
    // =====================================================

    $('#edit_base_unit_btn')
        .on(
            'click',
            function () {

                var currentBaseId =
                    baseUnitId();

                var $modalBase =
                    $('#modal_base_unit');

                destroySelect2(
                    $modalBase
                );

                $modalBase
                    .val(
                        currentBaseId
                    );

                $('#editBaseUnitModal')
                    .modal(
                        'show'
                    );


                window.setTimeout(
                    function () {

                        initSelect2(
                            $modalBase
                        );

                        $modalBase
                            .val(
                                currentBaseId
                            );

                        try {
                            $modalBase.trigger(
                                'change.select2'
                            );
                        } catch (ignored) {}

                        try {
                            $modalBase.select2(
                                'val',
                                currentBaseId
                            );
                        } catch (ignored) {}

                    },
                    150
                );


                return false;
            }
        );


    // =====================================================
    // CONFIRM BASE UNIT EDIT -> AUTO SAVE
    // =====================================================

    $('#confirm_base_unit_edit')
        .on(
            'click',
            function () {

                var newBaseId =
                    String(
                        $('#modal_base_unit')
                            .val() || ''
                    );


                if (!newBaseId) {

                    showAlert(
                        <?= json_encode(
                            $T(
                                $is_dual_unit
                                    ? 'အခြေခံအလေးချိန်ယူနစ်ကို ရွေးပါ။'
                                    : 'အငယ်ဆုံး ရေတွက်မည့်ယူနစ်ကို ရွေးပါ။',
                                $is_dual_unit
                                    ? 'Please select the primary weight unit.'
                                    : 'Please select the smallest counting unit.'
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?>
                    );

                    return false;
                }

                if (
                    isDualUnit &&
                    secondaryUnitId &&
                    newBaseId === secondaryUnitId
                ) {

                    showAlert(
                        <?= json_encode(
                            $T(
                                'သီးခြားရေတွက်မည့် ဒုတိယယူနစ်ကို အခြေခံအလေးချိန်ယူနစ်အဖြစ် မရွေးနိုင်ပါ။',
                                'The independent counting unit cannot be selected as the primary weight unit.'
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?>
                    );

                    return false;
                }


                var conflict =
                    false;


                $('.uc-unit-id')
                    .each(function () {

                        if (
                            String(
                                $(this).val() || ''
                            ) === newBaseId
                        ) {
                            conflict =
                                true;

                            return false;
                        }
                    });


                if (conflict) {

                    showAlert(
                        <?= json_encode(
                            $T(
                                'ဒီယူနစ်ကို ထုပ်ပိုးအဆင့်တစ်ခုတွင် အသုံးပြုထားပြီးသားဖြစ်ပါသည်။ အရင်ဆုံး အဲဒီထုပ်ပိုးအဆင့်ကို ပြင်ပါ။',
                                'This unit is already used in a packaging level. Edit that packaging level first.'
                            ),
                            JSON_UNESCAPED_UNICODE
                        ); ?>
                    );

                    return false;
                }


                if (
                    newBaseId ===
                    baseUnitId()
                ) {

                    $('#editBaseUnitModal')
                        .modal(
                            'hide'
                        );

                    return false;
                }


                $('#base_unit_id')
                    .val(
                        newBaseId
                    );


                $('#base_unit_display')
                    .text(
                        unitNameById(
                            newBaseId
                        )
                    );

                $('#dual_base_unit_display')
                    .text(
                        unitNameById(
                            newBaseId
                        )
                    );


                savedBaseUnitId =
                    newBaseId;


                refreshAllRows();


                $('#editBaseUnitModal')
                    .modal(
                        'hide'
                    );


                autoSaveForm();


                return false;
            }
        );


    $('#editBaseUnitModal')
        .on(
            'hidden.bs.modal',
            function () {

                destroySelect2(
                    $('#modal_base_unit')
                );

            }
        );


    // =====================================================
    // FORM SUBMIT
    // No visible Save button. Enter-key submit also auto-saves.
    // =====================================================

    $('#unit_conversion_form')
        .on(
            'submit',
            function (event) {

                if (autoSaving) {
                    return true;
                }

                event.preventDefault();

                autoSaveForm();

                return false;
            }
        );


    // =====================================================
    // MODAL CLEANUP
    // =====================================================

    $('#addPackagingModal')
        .on(
            'hidden.bs.modal',
            function () {

                destroySelect2(
                    $('#modal_second_unit')
                );

                $('#modal_second_unit')
                    .html(
                        '<option value="">' +
                        escapeHtml(
                            selectPackageText
                        ) +
                        '</option>'
                    )
                    .val('');

                $('#modal_chain_qty')
                    .val('');

                $('#modal_conversion_preview')
                    .hide()
                    .find('span')
                    .text('');

                editingRow = null;

                setModalAddMode();
            }
        );


    // =====================================================
    // INITIAL
    // =====================================================

    window.setTimeout(
        function () {

            initBaseSelect2();

            refreshAllRows();

            /*
             * Select2 v3/v4 can emit change events while it is being
             * initialized/restored. Wait briefly before enabling
             * user-triggered auto-save.
             */
            window.setTimeout(
                function () {

                    initializingPage =
                        false;

                },
                250
            );
        },
        120
    );

})(jQuery);
</script>
