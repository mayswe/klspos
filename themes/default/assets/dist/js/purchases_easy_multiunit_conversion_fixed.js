/*
 * ============================================================
 * KLSPOS Easy Multi-Unit Purchase Entry
 * ============================================================
 *
 * UI:
 *   Quantity
 *   Purchase Unit
 *   Unit Cost
 *
 * POST fields:
 *
 *   product_id[]
 *   primary_qty[]
 *   primary_unit[]
 *   secondary_qty[]
 *   secondary_unit[]
 *   cost[]
 *   transportation[]
 *
 * ============================================================
 */

(function ($) {

    'use strict';

    /* Live-file verification marker */
    window.KLS_PURCHASE_JS_VERSION =
        '2026-09-11-independent-dual-v3';


    // =========================================================
    // GLOBAL DATA
    // =========================================================

    var spoitems =
        window.spoitems || {};


    var productUnits =
        window.product_units || {};


    var productConversions =
        window.product_unit_conversions ||
        window.product_conversions ||
        {};


    var productUnitPrices =
        window.product_unit_prices ||
        {};


    var rawAllUnits =
        window.all_units ||
        {};


    var unitMap =
        {};


    var unitDataUrl =
        window.easyPurchaseUnitDataUrl ||
        (
            (window.base_url || '') +
            'purchases/product_unit_data/'
        );


    var unitDataCache =
        {};



    // =========================================================
    // HELPERS
    // =========================================================

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



    function numberValue(
        value,
        fallback
    ) {

        var parsed =
            parseFloat(value);


        if (isFinite(parsed)) {

            return parsed;
        }


        if (
            fallback !== undefined &&
            fallback !== null
        ) {

            return fallback;
        }


        return 0;
    }



    function formatNumber(
        value,
        decimals
    ) {

        var parsed =
            numberValue(
                value,
                0
            );


        var fixed =
            parsed.toFixed(

                typeof decimals ===
                'number'

                    ? decimals
                    : 4
            );


        return fixed
            .replace(
                /\.0+$/,
                ''
            )
            .replace(
                /(\.\d*?)0+$/,
                '$1'
            );
    }



    function formatMoneySafe(value) {

        if (
            typeof window.formatMoney ===
            'function'
        ) {

            return window.formatMoney(
                numberValue(
                    value,
                    0
                )
            );
        }


        return numberValue(
            value,
            0
        ).toFixed(2);
    }



    function formatDecimalSafe(value) {

        if (
            typeof window.formatDecimal ===
            'function'
        ) {

            return window.formatDecimal(
                numberValue(
                    value,
                    0
                )
            );
        }


        return numberValue(
            value,
            0
        ).toFixed(2);
    }



    function localStore(
        key,
        value
    ) {

        if (
            typeof window.store ===
            'function'
        ) {

            window.store(
                key,
                value
            );

        } else {

            localStorage.setItem(
                key,
                value
            );
        }
    }



    function localGet(key) {

        if (
            typeof window.get ===
            'function'
        ) {

            return window.get(key);
        }


        return localStorage.getItem(
            key
        );
    }



    function localRemove(key) {

        if (
            typeof window.remove ===
            'function'
        ) {

            window.remove(key);

        } else {

            localStorage.removeItem(
                key
            );
        }
    }



    // =========================================================
    // UNIT MAP
    // =========================================================

    function normalizeUnitMap() {

        unitMap = {};


        $.each(
            rawAllUnits,
            function (
                key,
                unit
            ) {


                if (
                    unit &&
                    typeof unit ===
                    'object'
                ) {


                    var id =
                        unit.id ||
                        unit.unit_id ||
                        key;


                    if (!id) {
                        return;
                    }


                    unitMap[
                        String(id)
                    ] = {

                        id:
                            String(id),

                        name:
                            unit.name ||
                            unit.unit_name ||
                            unit.label ||
                            unit.text ||
                            String(id),

                        code:
                            unit.code ||
                            unit.unit_code ||
                            ''
                    };


                } else if (
                    key &&
                    unit
                ) {


                    unitMap[
                        String(key)
                    ] = {

                        id:
                            String(key),

                        name:
                            String(unit),

                        code:
                            ''
                    };
                }

            }
        );
    }



    function unitName(unitId) {

        var unit =
            unitMap[
                String(unitId)
            ] ||
            null;


        return unit
            ? unit.name
            : '';
    }



    // =========================================================
    // UNIT DATA
    // =========================================================

    function applyUnitDataToItem(
        item,
        response
    ) {

        item =
            item || {};


        item.row =
            item.row || {};


        response =
            response || {};


        var productId =
            String(

                item.row.id ||
                item.id ||
                item.item_id ||
                response.product_id ||
                ''

            );


        if (!productId) {

            return item;
        }


        var baseUnit =
            response.base_unit ||
            {};


        var units =
            response.units ||
            [];


        var unitList =
            {};


        var conversions =
            {};

        item.row.is_dual_unit =
            response.is_dual_unit === true ||
            Number(response.is_dual_unit) === 1;

        item.row.secondary_unit_id =
            response.secondary_unit && response.secondary_unit.id
                ? String(response.secondary_unit.id)
                : String(item.row.secondary_unit_id || '');

        item.row.secondary_unit_name =
            response.secondary_unit && response.secondary_unit.name
                ? response.secondary_unit.name
                : (item.row.secondary_unit_name || '');



        // =====================================================
        // BASE UNIT
        // =====================================================

        if (baseUnit.id) {


            item.row.base_unit_id =
                String(
                    baseUnit.id
                );


            item.row.base_unit_name =
                baseUnit.name ||
                unitName(
                    baseUnit.id
                ) ||
                '-';


            unitList[
                String(
                    baseUnit.id
                )
            ] = {

                id:
                    String(
                        baseUnit.id
                    ),

                unit_id:
                    String(
                        baseUnit.id
                    ),

                name:
                    baseUnit.name ||
                    unitName(
                        baseUnit.id
                    ) ||
                    '-',

                unit_name:
                    baseUnit.name ||
                    unitName(
                        baseUnit.id
                    ) ||
                    '-',

                code:
                    baseUnit.code ||
                    '',

                multiplier:
                    1,

                operation_value:
                    1,

                operator:
                    '*'
            };


            conversions[
                String(
                    baseUnit.id
                )
            ] = {

                unit_id:
                    String(
                        baseUnit.id
                    ),

                unit_name:
                    baseUnit.name ||
                    unitName(
                        baseUnit.id
                    ) ||
                    '-',

                operator:
                    '*',

                operation_value:
                    1,

                multiplier:
                    1
            };
        }



        // =====================================================
        // CONVERSION UNITS
        // =====================================================

        $.each(
            units,
            function (
                _,
                unit
            ) {


                var unitId =
                    String(

                        unit.id ||
                        unit.unit_id ||
                        ''

                    );


                if (!unitId) {
                    return;
                }


                var operationValue =
                    numberValue(

                        unit.operation_value !==
                        undefined

                            ? unit.operation_value

                            : unit.multiplier,

                        1
                    );


                var operator =
                    unit.operator ||
                    '*';


                var multiplier =
                    operationValue;


                if (
                    operator === '/'
                ) {

                    multiplier =
                        operationValue > 0
                            ? 1 / operationValue
                            : 1;
                }


                multiplier =
                    multiplier > 0
                        ? multiplier
                        : 1;



                unitList[
                    unitId
                ] = {

                    id:
                        unitId,

                    unit_id:
                        unitId,

                    name:
                        unit.name ||
                        unit.unit_name ||
                        unitName(
                            unitId
                        ) ||
                        '-',

                    unit_name:
                        unit.name ||
                        unit.unit_name ||
                        unitName(
                            unitId
                        ) ||
                        '-',

                    code:
                        unit.code ||
                        unit.unit_code ||
                        '',

                    multiplier:
                        multiplier,

                    operation_value:
                        multiplier,

                    operator:
                        '*'
                };


                conversions[
                    unitId
                ] = {

                    unit_id:
                        unitId,

                    unit_name:
                        unit.name ||
                        unit.unit_name ||
                        unitName(
                            unitId
                        ) ||
                        '-',

                    operator:
                        '*',

                    operation_value:
                        multiplier,

                    multiplier:
                        multiplier
                };

            }
        );



        item.units =
            unitList;


        item.unit_conversions =
            conversions;


        productUnits[
            productId
        ] =
            unitList;


        productConversions[
            productId
        ] =
            conversions;


        unitDataCache[
            productId
        ] =
            response;


        return item;
    }



    // =========================================================
    // FETCH PRODUCT UNIT DATA
    // =========================================================

    function fetchProductUnitData(
        item,
        done
    ) {

        item =
            item || {};


        item.row =
            item.row || {};


        var productId =
            String(

                item.row.id ||
                item.id ||
                item.item_id ||
                ''

            );


        if (!productId) {

            done(item);

            return;
        }


        if (
            unitDataCache[
                productId
            ]
        ) {

            done(

                applyUnitDataToItem(

                    item,

                    unitDataCache[
                        productId
                    ]

                )

            );

            return;
        }



        $.ajax({

            url:
                unitDataUrl +
                encodeURIComponent(
                    productId
                ),

            type:
                'GET',

            dataType:
                'json',

            cache:
                false

        })

        .done(
            function (response) {


                if (
                    response &&
                    response.status ===
                    'success'
                ) {

                    applyUnitDataToItem(
                        item,
                        response
                    );
                }


                done(item);
            }
        )

        .fail(
            function () {

                done(item);
            }
        );
    }



    // =========================================================
    // HYDRATE STORED ITEMS
    // =========================================================

    function hydrateStoredItems(done) {

        var entries =
            [];


        var pending =
            0;


        $.each(
            spoitems,
            function (
                itemKey,
                item
            ) {


                item =
                    item || {};


                item.row =
                    item.row || {};


                var productId =
                    item.row.id ||
                    item.id ||
                    item.item_id;


                if (!productId) {
                    return;
                }


                entries.push({

                    key:
                        itemKey,

                    item:
                        item
                });

            }
        );


        pending =
            entries.length;


        if (!pending) {

            done();

            return;
        }


        $.each(
            entries,
            function (
                _,
                entry
            ) {


                fetchProductUnitData(

                    entry.item,

                    function (
                        hydratedItem
                    ) {


                        spoitems[
                            entry.key
                        ] =
                            hydratedItem;


                        pending -= 1;


                        if (
                            pending <= 0
                        ) {

                            persistItems();

                            done();
                        }

                    }

                );

            }
        );
    }



    // =========================================================
    // SOURCE
    // =========================================================

    function sourceForProduct(
        source,
        productId
    ) {

        return (
            source[
                productId
            ] ||
            source[
                String(
                    productId
                )
            ] ||
            {}
        );
    }



    // =========================================================
    // NORMALIZE CONVERSION
    // =========================================================

    function normalizeConversion(
        productId,
        unitId,
        value
    ) {

        var id =
            unitId;


        var multiplier =
            1;


        var operator =
            '*';


        var name =
            '';


        if (
            value &&
            typeof value ===
            'object'
        ) {


            id =
                value.unit_id ||
                value.secondary_unit_id ||
                value.to_unit_id ||
                value.id ||
                unitId;


            multiplier =
                numberValue(

                    value.operation_value !==
                    undefined

                        ? value.operation_value

                        : (
                            value.value !==
                            undefined

                                ? value.value

                                : value.multiplier
                        ),

                    1
                );


            operator =
                value.operator ||
                value.operation ||
                '*';


            name =
                value.unit_name ||
                value.name ||
                value.label ||
                value.text ||
                '';


        } else if (

            value !==
            undefined &&

            value !==
            null &&

            value !==
            ''

        ) {

            multiplier =
                numberValue(
                    value,
                    1
                );
        }


        if (
            operator === '/'
        ) {

            multiplier =
                multiplier > 0
                    ? 1 / multiplier
                    : 1;
        }


        multiplier =
            multiplier > 0
                ? multiplier
                : 1;


        return {

            id:
                String(id),

            multiplier:
                multiplier,

            name:
                name ||
                unitName(
                    id
                )
        };
    }



    // =========================================================
    // COLLECT PRODUCT UNITS
    // =========================================================

    function collectProductUnits(
        item,
        productId
    ) {

        item.row =
            item.row || {};


        var found =
            {};


        var result =
            [];


        var baseUnitId =
            item.row.base_unit_id ||
            item.row.primary_unit_id ||
            item.row.unit_id ||
            '';


        var baseUnitName =
            item.row.base_unit_name ||
            unitName(
                baseUnitId
            );



        function addUnit(
            id,
            name,
            multiplier
        ) {


            id =
                String(
                    id || ''
                );


            if (
                !id ||
                found[id]
            ) {

                return;
            }


            found[id] =
                true;


            var m =
                numberValue(
                    multiplier,
                    1
                );


            result.push({

                id:
                    id,

                name:
                    name ||
                    unitName(
                        id
                    ) ||
                    '-',

                multiplier:
                    m > 0
                        ? m
                        : 1
            });
        }



        if (baseUnitId) {

            addUnit(
                baseUnitId,
                baseUnitName,
                1
            );
        }



        var sources = [

            item.units ||
            {},

            sourceForProduct(
                productUnits,
                productId
            )
        ];


        $.each(
            sources,
            function (
                _,
                source
            ) {


                $.each(
                    source,
                    function (
                        key,
                        value
                    ) {


                        var id =
                            key;


                        var name =
                            '';


                        if (
                            value &&
                            typeof value ===
                            'object'
                        ) {


                            id =
                                value.id ||
                                value.unit_id ||
                                value.product_unit_id ||
                                value.secondary_unit_id ||
                                key;


                            name =
                                value.name ||
                                value.unit_name ||
                                value.label ||
                                value.text ||
                                unitName(
                                    id
                                );


                        } else {

                            name =
                                value ||
                                unitName(
                                    id
                                );
                        }


                        addUnit(
                            id,
                            name,
                            String(id) ===
                            String(baseUnitId)
                                ? 1
                                : 1
                        );

                    }
                );

            }
        );



        var conversionSources = [

            item.unit_conversions ||
            {},

            sourceForProduct(
                productConversions,
                productId
            )
        ];


        $.each(
            conversionSources,
            function (
                _,
                source
            ) {


                $.each(
                    source,
                    function (
                        key,
                        value
                    ) {


                        var conversion =
                            normalizeConversion(
                                productId,
                                key,
                                value
                            );


                        if (
                            !baseUnitId &&
                            conversion.multiplier ===
                            1
                        ) {

                            baseUnitId =
                                conversion.id;

                            baseUnitName =
                                conversion.name;
                        }


                        if (
                            found[
                                conversion.id
                            ]
                        ) {


                            $.each(
                                result,
                                function (
                                    index,
                                    existing
                                ) {


                                    if (
                                        String(
                                            existing.id
                                        ) ===
                                        String(
                                            conversion.id
                                        )
                                    ) {


                                        existing.multiplier =
                                            conversion.multiplier;


                                        if (
                                            conversion.name
                                        ) {

                                            existing.name =
                                                conversion.name;
                                        }


                                        return false;
                                    }

                                }
                            );


                        } else {


                            addUnit(

                                conversion.id,

                                conversion.name,

                                conversion.multiplier

                            );
                        }

                    }
                );

            }
        );



        if (
            !baseUnitId &&
            result.length
        ) {


            var oneToOne =
                null;


            $.each(
                result,
                function (
                    _,
                    candidate
                ) {


                    if (
                        numberValue(
                            candidate.multiplier,
                            1
                        ) === 1
                    ) {

                        oneToOne =
                            candidate;

                        return false;
                    }

                }
            );


            var selectedBase =
                oneToOne ||
                result[
                    result.length - 1
                ];


            baseUnitId =
                selectedBase.id;


            baseUnitName =
                selectedBase.name;
        }



        if (
            baseUnitId &&
            !found[
                String(
                    baseUnitId
                )
            ]
        ) {

            addUnit(
                baseUnitId,
                baseUnitName,
                1
            );
        }



        result.sort(
            function (
                a,
                b
            ) {


                if (
                    b.multiplier !==
                    a.multiplier
                ) {

                    return (
                        b.multiplier -
                        a.multiplier
                    );
                }


                return String(
                    a.name
                ).localeCompare(
                    String(
                        b.name
                    )
                );
            }
        );



        item.row.base_unit_id =
            baseUnitId ||
            '';


        item.row.base_unit_name =
            baseUnitName ||
            unitName(
                baseUnitId
            ) ||
            '-';


        item.row.available_purchase_units =
            result;


        return result;
    }



    // =========================================================
    // MULTIPLIER
    // =========================================================

    function conversionMultiplier(
        item,
        unitId
    ) {

        var multiplier =
            1;


        $.each(
            item.row.available_purchase_units ||
            [],
            function (
                _,
                unit
            ) {


                if (
                    String(
                        unit.id
                    ) ===
                    String(
                        unitId
                    )
                ) {

                    multiplier =
                        numberValue(
                            unit.multiplier,
                            1
                        );

                    return false;
                }

            }
        );


        return multiplier > 0
            ? multiplier
            : 1;
    }



    // =========================================================
    // UNIT LABEL
    // =========================================================

    function unitLabel(
        item,
        unit
    ) {

        var label =
            unit.name ||
            '-';


        var baseName =
            item.row.base_unit_name ||
            '-';


        if (
            numberValue(
                unit.multiplier,
                1
            ) !== 1
        ) {


            label +=
                ' (1 = ' +
                formatNumber(
                    unit.multiplier,
                    4
                ) +
                ' ' +
                baseName +
                ')';
        }


        return label;
    }



    // =========================================================
    // UNIT OPTIONS
    // =========================================================

    function buildUnitOptions(
        item,
        selectedUnitId
    ) {

        var html =
            '';


        $.each(
            item.row.available_purchase_units ||
            [],
            function (
                _,
                unit
            ) {


                html +=
                    '<option value="' +
                    escapeHtml(
                        unit.id
                    ) +
                    '"' +


                    (
                        String(
                            unit.id
                        ) ===
                        String(
                            selectedUnitId
                        )

                            ? ' selected'
                            : ''
                    ) +


                    ' data-multiplier="' +
                    escapeHtml(
                        unit.multiplier
                    ) +
                    '">' +


                    escapeHtml(
                        unitLabel(
                            item,
                            unit
                        )
                    ) +


                    '</option>';

            }
        );


        return html;
    }



    // =========================================================
    // DEFAULT PURCHASE UNIT
    // =========================================================

    function defaultPurchaseUnit(
        item
    ) {

        var units =
            item.row.available_purchase_units ||
            [];


        var selected =
            item.row.purchase_unit ||
            item.row.primary_unit ||
            '';


        if (selected) {


            var exists =
                false;


            $.each(
                units,
                function (
                    _,
                    unit
                ) {


                    if (
                        String(
                            unit.id
                        ) ===
                        String(
                            selected
                        )
                    ) {

                        exists =
                            true;

                        return false;
                    }

                }
            );


            if (exists) {

                return String(
                    selected
                );
            }
        }


        return units.length

            ? String(
                units[0].id
            )

            : String(
                item.row.base_unit_id ||
                ''
            );
    }



    // =========================================================
    // CONFIGURED UNIT COST
    // =========================================================

    function configuredUnitCost(
        productId,
        unitId
    ) {

        var source =
            sourceForProduct(
                productUnitPrices,
                productId
            );


        var value =
            source[
                unitId
            ] ||
            source[
                String(
                    unitId
                )
            ];


        if (
            value &&
            typeof value ===
            'object'
        ) {

            value =
                value.cost ||
                value.purchase_cost ||
                value.price ||
                value.unit_price ||
                0;
        }


        return numberValue(
            value,
            0
        );
    }



    // =========================================================
    // SUGGESTED UNIT COST
    // =========================================================

    function suggestedUnitCost(
        item,
        productId,
        unitId
    ) {

        var configured =
            configuredUnitCost(
                productId,
                unitId
            );


        if (
            configured > 0
        ) {

            return configured;
        }


        var baseCost =
            numberValue(
                item.row.base_cost,
                0
            );


        if (!baseCost) {


            baseCost =
                numberValue(
                    item.row.cost,
                    0
                );


            item.row.base_cost =
                baseCost;
        }


        return (
            baseCost *
            conversionMultiplier(
                item,
                unitId
            )
        );
    }


    // =========================================================
    // DUAL UNIT HELPERS
    // =========================================================

    function isDualUnit(item) {

        item = item || {};
        item.row = item.row || {};

        return (
            item.row.is_dual_unit === true ||
            Number(item.row.is_dual_unit) === 1
        ) && String(item.row.secondary_unit_id || '') !== '';
    }


    function dualSecondaryUnitId(item) {

        return isDualUnit(item)
            ? String(item.row.secondary_unit_id || '')
            : '';
    }


    function dualSecondaryUnitName(item) {

        var unitId = dualSecondaryUnitId(item);
        var name = item.row.secondary_unit_name || unitName(unitId) || '-';

        $.each(
            item.row.available_purchase_units || [],
            function (_, unit) {
                if (String(unit.id) === unitId) {
                    name = unit.name || name;
                    return false;
                }
            }
        );

        return name;
    }


    function secondaryBaseQuantity(item) {
        // Independent count (piece/coil) is not converted into weight stock.
        return 0;
    }



    // =========================================================
    // BASE QTY
    // =========================================================

    function baseQuantity(item) {

        return (

            numberValue(
                item.row.purchase_qty,
                0
            )

            *

            conversionMultiplier(
                item,
                item.row.purchase_unit
            )
        );
    }



    // =========================================================
    // SUBTOTAL
    // =========================================================

    function lineSubtotal(item) {

        return numberValue(item.row.purchase_qty, 0) *
            numberValue(item.row.cost, 0);
    }



    // =========================================================
    // PREVIEW
    // =========================================================

    function linePreview(item) {

        var selectedName =
            unitName(
                item.row.purchase_unit
            );


        $.each(
            item.row.available_purchase_units ||
            [],
            function (
                _,
                unit
            ) {


                if (
                    String(
                        unit.id
                    ) ===
                    String(
                        item.row.purchase_unit
                    )
                ) {

                    selectedName =
                        unit.name;

                    return false;
                }

            }
        );


        var preview = (

            formatNumber(
                item.row.purchase_qty,
                4
            )

            +

            ' '

            +

            (
                selectedName ||
                '-'
            )

            +

            ' = '

            +

            formatNumber(
                baseQuantity(
                    item
                ),
                4
            )

            +

            ' '

            +

            (item.row.base_unit_name || '-')
        );

        if (isDualUnit(item)) {
            preview =
                formatNumber(item.row.purchase_qty, 4) + ' ' +
                (selectedName || '-') + ' = ' +
                formatNumber(baseQuantity(item), 4) + ' ' +
                (item.row.base_unit_name || '-') + ' | ' +
                formatNumber(item.row.secondary_qty, 4) + ' ' +
                dualSecondaryUnitName(item);
        }

        return preview;
    }



    // =========================================================
    // UNIQUE KEY
    // =========================================================

    function uniqueItemKey(
        productId
    ) {

        var key;

        do {
            key =
                String(productId) +
                '_' +
                Date.now() +
                '_' +
                Math.floor(
                    Math.random() *
                    1000000
                );
        } while (
            Object.prototype.hasOwnProperty.call(
                spoitems,
                key
            )
        );

        return key;
    }



    function itemKeyForNewLine(
        item,
        productId
    ) {

        /*
         * Purchase တစ်ခုထဲတွင် Product တူသော်လည်း ယူနစ်၊ ဝယ်ဈေးနှင့်
         * FOC အရေအတွက် မတူသော line များ သီးခြားထည့်နိုင်ရန်
         * ရွေးချယ်မှုတိုင်း unique key အသစ်ကို အမြဲသုံးမည်။
         */
        return uniqueItemKey(
            productId
        );
    }



    // =========================================================
    // PERSIST
    // =========================================================

    function persistItems() {

        localStore(

            'spoitems',

            JSON.stringify(
                spoitems
            )

        );


        window.spoitems =
            spoitems;
    }



    // =========================================================
    // TRANSPORT TOTAL
    // =========================================================

    function calculateTransportTotal() {

        var total =
            0;


        $.each(
            spoitems,
            function (
                _,
                item
            ) {


                item =
                    item || {};


                item.row =
                    item.row || {};


                total +=
                    numberValue(
                        item.row.transportation,
                        0
                    );

            }
        );


        return total;
    }



    // =========================================================
    // GRAND TOTAL
    // =========================================================

    function calculateGrandTotal() {
    
        var itemsTotal = 0;
    
        $.each(
            spoitems,
            function (_, item) {
    
                item = item || {};
                item.row = item.row || {};
    
                itemsTotal += lineSubtotal(item);
            }
        );
    
        // သယ်ယူပို့ဆောင်ခ စုစုပေါင်း
         var transportTotal = calculateTransportTotal();
         
        // Grand Total mirrors backend logic:
        // use row transport sum if any row has transport,
        // otherwise fall back to the manually typed #delivery value.
        var manualDelivery = numberValue($('#delivery').val(), 0);
        
        var deliveryForGrandTotal =
            transportTotal > 0
                ? transportTotal
                : manualDelivery;
                
        // ပစ္စည်းကျသင့်ငွေ + သယ်ယူပို့ဆောင်ခ
        var grandTotal =
            itemsTotal + deliveryForGrandTotal;
        
        $('#gtotal')
            .text(
                formatMoneySafe(itemsTotal)
            );
    
    
        $('#erp_grand_total')
            .text(
                formatMoneySafe(grandTotal)
            );
    
    
        // Delivery hidden/summary value
        
            
        if (transportTotal > 0) {
            $('#delivery')
                .val(formatDecimalSafe(transportTotal));
        }
    
    
        /*
         * Paid Now ကို ဒီနေရာမှာ
         * အလိုအလျောက် မဖြည့်ပါနှင့်။
         * User ထည့်ထားတဲ့ value ကိုပဲထားမယ်။
         */
    
    
        return grandTotal;
    }



    // =========================================================
    // EMPTY ROW
    // =========================================================

    function emptyRowHtml() {

        return (

            '<tr class="po-empty-row">' +

            '<td colspan="8" class="text-center text-muted po-empty-cell">' +

            '<i class="fa fa-search"></i> ' +

            escapeHtml(
                window.easyPurchaseLabels.emptyItems
            ) +

            '</td>' +

            '</tr>'

        );
    }



    // =========================================================
    // PURCHASE UNIT SELECT2
    // =========================================================

    function initialisePurchaseUnitSelect2(
        $context
    ) {

        if (!$.fn.select2) {

            return;
        }


        var $selects =

            $context &&
            $context.length

                ? $context.find(
                    'select.purchase-unit'
                )

                : $('#poTable select.purchase-unit');


        $selects.each(
            function () {


                var $select =
                    $(this);


                var selectedValue =
                    $select.val();


                try {

                    if (
                        $select.data('select2')
                    ) {

                        $select.select2(
                            'destroy'
                        );
                    }

                } catch (ignored) {}


                $select
                    .siblings(
                        '.select2-container'
                    )
                    .remove();


                $select
                    .removeClass(
                        'select2-hidden-accessible select2-offscreen'
                    )
                    .removeAttr(
                        'data-select2-id aria-hidden tabindex'
                    )
                    .css(
                        'width',
                        '100%'
                    );


                $select
                    .find('option')
                    .removeAttr(
                        'data-select2-id'
                    );


                try {

                    $select.select2({

                        width:
                            '100%',

                        dropdownAutoWidth:
                            false,

                        minimumResultsForSearch:
                            0

                    });

                } catch (error) {


                    try {

                        $select.select2();

                    } catch (ignored) {

                        return;
                    }

                }


                if (

                    selectedValue !==
                    null &&

                    selectedValue !==
                    undefined

                ) {


                    $select.val(
                        String(
                            selectedValue
                        )
                    );


                    try {

                        $select.trigger(
                            'change.select2'
                        );

                    } catch (ignored) {}


                    try {

                        $select.select2(
                            'val',
                            String(
                                selectedValue
                            )
                        );

                    } catch (ignored) {}

                }


                $select
                    .next(
                        '.select2-container'
                    )
                    .css(
                        'width',
                        '100%'
                    );

            }
        );
    }



    // =========================================================
    // LOAD ITEMS
    // =========================================================

    function loadItems() {

        var stored =
            localGet(
                'spoitems'
            );


        if (stored) {

            try {

                spoitems =
                    JSON.parse(
                        stored
                    ) ||
                    {};

            } catch (error) {

                spoitems =
                    {};
            }
        }



        var $tbody =
            $('#poTable tbody');


        $tbody.empty();


        var hasItems =
            false;


        var itemKeys = Object.keys(spoitems).reverse();
        $.each(
            itemKeys,
            function (
            _,
                itemKey
            ) {

                var item = spoitems[itemKey];
                item =
                    item || {};


                item.row =
                    item.row || {};


                var productId =
                    item.row.id ||
                    item.id ||
                    item.item_id;


                if (!productId) {

                    return;
                }


                hasItems =
                    true;


                collectProductUnits(
                    item,
                    productId
                );



                item.row.purchase_qty =
                    numberValue(

                        item.row.purchase_qty !==
                        undefined

                            ? item.row.purchase_qty

                            : item.row.primary_qty,

                        1
                    );



                item.row.purchase_unit =
                    defaultPurchaseUnit(
                        item
                    );


                item.row.primary_qty =
                    item.row.purchase_qty;


                item.row.primary_unit =
                    item.row.purchase_unit;


                item.row.secondary_qty =
                    isDualUnit(item)
                        ? numberValue(item.row.secondary_qty, 0)
                        : 0;


                item.row.secondary_unit =
                    isDualUnit(item)
                        ? dualSecondaryUnitId(item)
                        : 0;


                



                if (

                    !item.row.cost ||

                    item.row.recalculate_cost ===
                    true

                ) {


                    item.row.cost =
                        suggestedUnitCost(

                            item,

                            productId,

                            item.row.purchase_unit

                        );


                    item.row.recalculate_cost =
                        false;
                }



                var productName =
                    item.row.name ||
                    item.label ||
                    '-';


                var productCode =
                    item.row.code ||
                    item.code ||
                    '-';


                var subtotal =
                    lineSubtotal(
                        item
                    );


                var rowId =

                    'po_row_' +

                    String(
                        itemKey
                    ).replace(
                        /[^a-zA-Z0-9_-]/g,
                        '_'
                    );



                var html =
                    '';



                html +=

                    '<tr ' +

                    'id="' +
                    escapeHtml(
                        rowId
                    ) +
                    '" ' +

                    'class="purchase-item-row" ' +

                    'data-item-key="' +
                    escapeHtml(
                        itemKey
                    ) +
                    '" ' +

                    'data-product-id="' +
                    escapeHtml(
                        productId
                    ) +
                    '">' ;


                // =============================================
                // PRODUCT
                // =============================================

                html +=
                    '<td class="po-product-cell">';


                html +=

                    '<input ' +
                    'type="hidden" ' +
                    'name="product_id[]" ' +
                    'value="' +
                    escapeHtml(
                        productId
                    ) +
                    '">';

                html +=

                    '<div class="po-product-name">' +
                    escapeHtml(
                        productName
                    ) +
                    '</div>';


                html +=

                    '<div class="po-product-code">' +
                    escapeHtml(
                        productCode
                    ) +
                    '</div>';


                html +=
                    '</td>';



                // =============================================
                // QTY
                // =============================================

                html +=

                    '<td data-label="' +
                    escapeHtml(
                        window.easyPurchaseLabels.quantity
                    ) +
                    '">';


                html +=

                    '<input ' +
                    'type="number" ' +
                    'class="form-control purchase-qty" ' +
                    'min="0.0001" ' +
                    'step="0.0001" ' +
                    'value="' +
                    escapeHtml(
                        formatNumber(
                            item.row.purchase_qty,
                            4
                        )
                    ) +
                    '">';


                if (isDualUnit(item)) {

                    html +=
                        '<div class="dual-qty-extra">' +
                        '<span class="dual-qty-plus">+</span>' +
                        '<input type="number" ' +
                        'class="form-control secondary-purchase-qty" ' +
                        'min="0" step="0.0001" ' +
                        'value="' +
                        escapeHtml(formatNumber(item.row.secondary_qty, 4)) +
                        '">' +
                        '<span class="dual-qty-unit">' +
                        escapeHtml(dualSecondaryUnitName(item)) +
                        '</span>' +
                        '</div>';
                }


                html +=

                    '<input ' +
                    'type="hidden" ' +
                    'name="primary_qty[]" ' +
                    'class="submit-primary-qty" ' +
                    'value="' +
                    escapeHtml(
                        formatNumber(
                            item.row.purchase_qty,
                            4
                        )
                    ) +
                    '">';


                html +=

                    '<input ' +
                    'type="hidden" ' +
                    'name="secondary_qty[]" ' +
                    'class="submit-secondary-qty" ' +
                    'value="' +
                    escapeHtml(formatNumber(item.row.secondary_qty, 4)) +
                    '">';


                html +=
                    '</td>';



                // =============================================
                // UNIT
                // =============================================

                html +=

                    '<td data-label="' +
                    escapeHtml(
                        window.easyPurchaseLabels.unit
                    ) +
                    '">';


                html +=

                    '<select ' +
                    'class="form-control purchase-unit">' +

                    buildUnitOptions(
                        item,
                        item.row.purchase_unit
                    ) +

                    '</select>';


                html +=

                    '<input ' +
                    'type="hidden" ' +
                    'name="primary_unit[]" ' +
                    'class="submit-primary-unit" ' +
                    'value="' +
                    escapeHtml(
                        item.row.purchase_unit
                    ) +
                    '">';


                html +=

                    '<input ' +
                    'type="hidden" ' +
                    'name="secondary_unit[]" ' +
                    'class="submit-secondary-unit" ' +
                    'value="' +
                    escapeHtml(item.row.secondary_unit) +
                    '">';


                html +=
                    '</td>';



                // =============================================
                // COST
                // =============================================

                html +=

                    '<td data-label="' +
                    escapeHtml(
                        window.easyPurchaseLabels.unitCost
                    ) +
                    '">';


                html +=

                    '<input ' +
                    'type="number" ' +
                    'class="form-control purchase-cost" ' +
                    'name="cost[]" ' +
                    'min="0" ' +
                    'step="0.01" ' +
                    'value="' +
                    escapeHtml(
                        numberValue(
                            item.row.cost,
                            0
                        ).toFixed(2)
                    ) +
                    '">';


                html +=
                    '</td>';



                // =============================================
                // BASE QTY PREVIEW
                // =============================================

                html +=

                    '<td ' +
                    'class="po-base-preview" ' +
                    'data-label="' +
                    escapeHtml(
                        window.easyPurchaseLabels.baseQuantity
                    ) +
                    '">';


                html +=

                    '<div class="base-preview-text">' +
                    escapeHtml(
                        linePreview(
                            item
                        )
                    ) +
                    '</div>';


                html +=

                    '<input ' +
                    'type="hidden" ' +
                    'name="converted_base_qty[]" ' +
                    'class="converted-base-qty" ' +
                    'value="' +
                    escapeHtml(
                        formatNumber(
                            baseQuantity(
                                item
                            ),
                            4
                        )
                    ) +
                    '">';


                html +=
                    '</td>';



                // =============================================
                // SUBTOTAL
                // =============================================

                html +=

                    '<td ' +
                    'class="text-right po-subtotal" ' +
                    'data-label="' +
                    escapeHtml(
                        window.easyPurchaseLabels.subtotal
                    ) +
                    '">';


                html +=

                    '<span ' +
                    'class="ssubtotal" ' +
                    'data-subtotal="' +
                    escapeHtml(
                        subtotal
                    ) +
                    '">' +

                    formatMoneySafe(
                        subtotal
                    ) +

                    '</span>';


                html +=
                    '</td>';



                // =============================================
                // TRANSPORT
                // =============================================

                html +=

                    '<td data-label="' +
                    escapeHtml(
                        window.easyPurchaseLabels.transportation
                    ) +
                    '">';


                html +=
    '<input ' +
    'type="number" ' +
    'class="form-control transportation" ' +
    'name="transportation[]" ' +
    'min="0" ' +
    'step="0.01" ' +
    'placeholder="0.00" ' +
    'value="' +
    (
        item.row.transportation !== undefined &&
        item.row.transportation !== null
            ? escapeHtml(item.row.transportation)
            : ''
    ) +
    '">';


                html +=
                    '</td>';



                // =============================================
                // DELETE
                // =============================================

                html +=

                    '<td class="text-center po-delete-cell">';


                html +=

                    '<button ' +
                    'type="button" ' +
                    'class="btn btn-sm btn-danger spodel" ' +
                    'title="' +
                    escapeHtml(
                        window.easyPurchaseLabels.delete
                    ) +
                    '">' +

                    '<i class="fa fa-times"></i>' +

                    '</button>';


                html +=
                    '</td>';


                html +=
                    '</tr>';



                $tbody.append(
                    html
                );

            }
        );



        if (!hasItems) {

            $tbody.html(
                emptyRowHtml()
            );
        }



        initialisePurchaseUnitSelect2(
            $tbody
        );


        persistItems();


        calculateGrandTotal();
    }



    // =========================================================
    // ADD ORDER ITEM
    // =========================================================

    function addOrderItem(item) {

        item =
            item || {};


        item.row =
            item.row || {};


        var productId =
            item.row.id ||
            item.id ||
            item.item_id;


        if (!productId) {

            return false;
        }



        if (!item.row.id) {

            item.row.id =
                productId;
        }


        if (!item.row.code) {

            item.row.code =
                item.code ||
                '';
        }


        if (!item.row.name) {

            item.row.name =
                item.label ||
                item.name ||
                '';
        }



        if (item.units) {

            productUnits[
                String(
                    productId
                )
            ] =
                item.units;
        }



        if (item.unit_prices) {

            productUnitPrices[
                String(
                    productId
                )
            ] =
                item.unit_prices;
        }



        if (
            item.unit_conversions
        ) {

            productConversions[
                String(
                    productId
                )
            ] =
                item.unit_conversions;
        }



        var itemKey =
            itemKeyForNewLine(
                item,
                productId
            );



        /*
         * Product တူနေသော်လည်း ပထမ row quantity ကို မပေါင်းပါနှင့်။
         * ဖာဝယ်မှုနှင့် FOC ထုပ်များကို ယူနစ်/ဈေးသီးခြားထည့်နိုင်ရန်
         * selection တစ်ကြိမ်စီကို line အသစ်တစ်ကြောင်းအဖြစ် အမြဲသိမ်းမည်။
         */
        item.row.base_cost =
            numberValue(
                item.row.cost,
                0
            );


        collectProductUnits(
            item,
            productId
        );


        item.row.purchase_unit =
            defaultPurchaseUnit(
                item
            );


        item.row.purchase_qty =
            1;


        item.row.primary_qty =
            1;


        item.row.primary_unit =
            item.row.purchase_unit;


        item.row.secondary_qty =
            0;


        item.row.secondary_unit =
            isDualUnit(item)
                ? dualSecondaryUnitId(item)
                : 0;


        item.row.cost =
            suggestedUnitCost(

                item,

                productId,

                item.row.purchase_unit

            );


        spoitems[
            itemKey
        ] =
            item;



        persistItems();


        loadItems();


        return true;
    }



    // =========================================================
    // UPDATE ITEM FROM ROW
    // =========================================================

    function updateItemFromRow(
        $row
    ) {

        var itemKey =
            String(
                $row.data(
                    'item-key'
                )
            );


        var item =
            spoitems[
                itemKey
            ];


        if (!item) {

            return;
        }


        item.row =
            item.row ||
            {};


        var productId =
            item.row.id;


        var oldUnitId =
            String(
                item.row.purchase_unit ||
                ''
            );


        var newUnitId =
            String(
                $row
                    .find(
                        '.purchase-unit'
                    )
                    .val() ||
                ''
            );


        var unitChanged =
            oldUnitId !==
            newUnitId;



        item.row.purchase_qty =
            numberValue(

                $row
                    .find(
                        '.purchase-qty'
                    )
                    .val(),

                0
            );


        item.row.purchase_unit =
            newUnitId;


        item.row.primary_qty =
            item.row.purchase_qty;


        item.row.primary_unit =
            newUnitId;


        item.row.secondary_qty =
            isDualUnit(item)
                ? numberValue(
                    $row.find('.secondary-purchase-qty').val(),
                    0
                )
                : 0;


        item.row.secondary_unit =
            isDualUnit(item)
                ? dualSecondaryUnitId(item)
                : 0;


        item.row.transportation =
            numberValue(

                $row
                    .find(
                        '.transportation'
                    )
                    .val(),

                0
            );



        if (unitChanged) {


            item.row.cost =
                suggestedUnitCost(

                    item,

                    productId,

                    newUnitId

                );


            $row
                .find(
                    '.purchase-cost'
                )
                .val(
                    numberValue(
                        item.row.cost,
                        0
                    ).toFixed(2)
                );


        } else {


            item.row.cost =
                numberValue(

                    $row
                        .find(
                            '.purchase-cost'
                        )
                        .val(),

                    0
                );
        }



        // =====================================================
        // IMPORTANT SUBMIT FIELDS
        // =====================================================

        $row
            .find(
                '.submit-primary-qty'
            )
            .val(
                formatNumber(
                    item.row.purchase_qty,
                    4
                )
            );


        $row
            .find(
                '.submit-primary-unit'
            )
            .val(
                newUnitId
            );


        $row
            .find(
                'input[name="secondary_qty[]"]'
            )
            .val(
                formatNumber(item.row.secondary_qty, 4)
            );


        $row
            .find(
                'input[name="secondary_unit[]"]'
            )
            .val(
                item.row.secondary_unit
            );


        $row
            .find(
                '.converted-base-qty'
            )
            .val(
                formatNumber(
                    baseQuantity(
                        item
                    ),
                    4
                )
            );


        $row
            .find(
                '.base-preview-text'
            )
            .text(
                linePreview(
                    item
                )
            );



        var subtotal =
            lineSubtotal(
                item
            );


        $row
            .find(
                '.ssubtotal'
            )
            .attr(
                'data-subtotal',
                subtotal
            )
            .text(
                formatMoneySafe(
                    subtotal
                )
            );


        persistItems();


        calculateGrandTotal();
    }



    // =========================================================
    // AUTOCOMPLETE
    // =========================================================

    var productSelectionBusy = false;


    function processSelectedProduct(
        inputElement,
        selectedItem
    ) {

        if (
            productSelectionBusy ||
            !selectedItem ||
            Number(selectedItem.id) === 0
        ) {

            return false;
        }


        productSelectionBusy = true;


        var $input =
            $(inputElement);


        $input.prop(
            'disabled',
            true
        );


        fetchProductUnitData(

            selectedItem,

            function (hydratedItem) {

                addOrderItem(
                    hydratedItem
                );


                $input
                    .val('')
                    .prop(
                        'disabled',
                        false
                    )
                    .focus();


                /*
                 * jQuery UI က autocompleteselect event ပြီးမှ options.select
                 * callback ကို ထပ်ခေါ်သောကြောင့် current event loop ပြီးမှသာ
                 * busy flag ကို ပြန်ဖွင့်မည်။ ဒါဖြင့် တစ်ကြိမ်ရွေးလျှင်
                 * row တစ်ကြောင်းတည်းသာ ဝင်ပြီး နောက်တစ်ကြိမ်ရွေးလျှင်
                 * row အသစ်ထပ်ဝင်နိုင်မည်။
                 */
                window.setTimeout(
                    function () {
                        productSelectionBusy = false;
                    },
                    0
                );
            }
        );


        return true;
    }

    function setupAutocomplete() {

        if (!$.fn.autocomplete) {

            return;
        }


        $('#add_item')
            .autocomplete({


                source:

                    (window.base_url || '') +

                    'purchases/suggestions',


                minLength:
                    1,


                autoFocus:
                    false,


                delay:
                    200,



                response:
                    function (
                        event,
                        ui
                    ) {


                        var content =
                            ui.content ||
                            [];


                        if (

                            !content.length ||

                            (
                                content.length === 1 &&
                                Number(
                                    content[0].id
                                ) === 0
                            )

                        ) {


                            if (
                                $.trim(
                                    $(this).val()
                                ) !== ''
                            ) {


                                bootbox.alert(

                                    window.lang &&
                                    window.lang.no_match_found

                                        ? window.lang.no_match_found

                                        : window.easyPurchaseLabels.noMatch

                                );
                            }


                            $(this).val(
                                ''
                            );


                            return;
                        }



                        if (

                            content.length === 1 &&

                            Number(
                                content[0].id
                            ) !== 0

                        ) {


                            ui.item =
                                content[0];


                            $(this)
                                .data(
                                    'ui-autocomplete'
                                )
                                ._trigger(

                                    'select',

                                    'autocompleteselect',

                                    ui

                                );


                            $(this)
                                .autocomplete(
                                    'close'
                                );
                        }

                    },



                select:
                    function (
                        event,
                        ui
                    ) {


                        event.preventDefault();


                        processSelectedProduct(
                            this,
                            ui && ui.item
                                ? ui.item
                                : null
                        );

                    }

            });



        /*
         * jQuery UI version မတူမှုကြောင့် options.select callback မခေါ်သည့်
         * browser များအတွက် native autocomplete selection event ကိုပါ
         * တိုက်ရိုက်ဖမ်းထားသည်။ Shared busy flag က double-add ကိုကာသည်။
         */
        $('#add_item')
            .off(
                'autocompleteselect.klsPurchase'
            )
            .on(
                'autocompleteselect.klsPurchase',
                function (event, ui) {

                    event.preventDefault();

                    processSelectedProduct(
                        this,
                        ui && ui.item
                            ? ui.item
                            : null
                    );
                }
            );



        $('#add_item')
            .on(
                'keypress',
                function (event) {


                    if (
                        event.keyCode ===
                        13
                    ) {


                        event.preventDefault();


                        $(this)
                            .autocomplete(
                                'search'
                            );
                    }

                }
            );
    }



    // =========================================================
    // VALIDATE ITEMS
    // =========================================================

    function validateItems() {

        var valid =
            true;


        var hasItems =
            false;



        $('#poTable tbody .purchase-item-row')
            .each(
                function () {


                    hasItems =
                        true;


                    var $row =
                        $(this);


                    var qty =
                        numberValue(

                            $row
                                .find(
                                    '.purchase-qty'
                                )
                                .val(),

                            0
                        );


                    var unit =
                        $row
                            .find(
                                '.purchase-unit'
                            )
                            .val();


                    var rawCost =
                        $row
                            .find(
                                '.purchase-cost'
                            )
                            .val();


                    var cost =
                        parseFloat(
                            rawCost
                        );


                    var rowInvalid =

                        qty <= 0 ||

                        !unit ||

                        !isFinite(
                            cost
                        ) ||

                        cost < 0;


                    $row.toggleClass(
                        'has-error',
                        rowInvalid
                    );


                    if (
                        rowInvalid
                    ) {

                        valid =
                            false;
                    }

                }
            );



        if (!hasItems) {


            bootbox.alert(

                window.easyPurchaseLabels
                    .addAtLeastOne

            );


            return false;
        }



        if (!valid) {


            bootbox.alert(

                window.easyPurchaseLabels
                    .checkItems

            );
        }


        return valid;
    }



    // =========================================================
    // SAVE PURCHASE
    // =========================================================

    function savePurchase() {


        var form =
            document.getElementById(
                'purchase_add_form'
            );


        var $button =
            $('#save_purchase_btn');


        if (!form) {


            bootbox.alert(
                'Purchase form not found.'
            );


            return false;
        }



        // =====================================================
        // DATE
        // =====================================================

        var date =
            $.trim(
                $('#date').val() ||
                ''
            );


        if (!date) {


            bootbox.alert(

                window.easyPurchaseLabels.selectDate ||
                'Please select date and time.'

            );


            $('#date').focus();


            return false;
        }



        // =====================================================
        // SUPPLIER
        // =====================================================

        var supplier =
            $('#supplier').val();


        if (

            !supplier ||

            String(
                supplier
            ) === '0'

        ) {


            bootbox.alert(

                window.easyPurchaseLabels.selectSupplier ||
                'Please select a supplier.'

            );


            try {

                $('#supplier')
                    .select2(
                        'open'
                    );

            } catch (error) {


                $('#supplier')
                    .focus();
            }


            return false;
        }



        // =====================================================
        // STORE
        // =====================================================

        var store =
            $('#store').val();


        if (

            !store ||

            String(
                store
            ) === '0'

        ) {


            bootbox.alert(

                window.easyPurchaseLabels.selectStore ||
                'Please select a receiving location.'

            );


            try {

                $('#store')
                    .select2(
                        'open'
                    );

            } catch (error) {


                $('#store')
                    .focus();
            }


            return false;
        }



        // =====================================================
        // ITEM ROWS
        // =====================================================

        var $rows =
            $('#poTable tbody .purchase-item-row');


        if (!$rows.length) {


            bootbox.alert(

                window.easyPurchaseLabels
                    .addAtLeastOne

            );


            $('#add_item').focus();


            return false;
        }



        // =====================================================
        // UPDATE ALL ROWS
        // =====================================================

        $rows.each(
            function () {


                updateItemFromRow(
                    $(this)
                );

            }
        );



        // =====================================================
        // VALIDATE
        // =====================================================

        if (
            !validateItems()
        ) {

            return false;
        }



        // =====================================================
        // FORCE POST VALUES
        // =====================================================

        $rows.each(
            function () {


                var $row =
                    $(this);


                var qty =
                    numberValue(

                        $row
                            .find(
                                '.purchase-qty'
                            )
                            .val(),

                        0
                    );


                var unit =
                    String(

                        $row
                            .find(
                                '.purchase-unit'
                            )
                            .val() ||

                        ''

                    );


                var cost =
                    numberValue(

                        $row
                            .find(
                                '.purchase-cost'
                            )
                            .val(),

                        0
                    );


                var transport =
                    numberValue(

                        $row
                            .find(
                                '.transportation'
                            )
                            .val(),

                        0
                    );

                var itemKey =
                    String($row.data('item-key'));

                var currentItem =
                    spoitems[itemKey] || { row: {} };

                var secondaryQty =
                    isDualUnit(currentItem)
                        ? numberValue(
                            $row.find('.secondary-purchase-qty').val(),
                            0
                        )
                        : 0;

                var secondaryUnit =
                    isDualUnit(currentItem)
                        ? dualSecondaryUnitId(currentItem)
                        : 0;



                $row
                    .find(
                        'input[name="primary_qty[]"]'
                    )
                    .val(
                        qty
                    );


                $row
                    .find(
                        'input[name="primary_unit[]"]'
                    )
                    .val(
                        unit
                    );


                $row
                    .find(
                        'input[name="secondary_qty[]"]'
                    )
                    .val(
                        secondaryQty
                    );


                $row
                    .find(
                        'input[name="secondary_unit[]"]'
                    )
                    .val(
                        secondaryUnit
                    );


                $row
                    .find(
                        'input[name="cost[]"]'
                    )
                    .val(
                        cost
                    );


                $row
                    .find(
                        'input[name="transportation[]"]'
                    )
                    .val(
                        transport
                    );

            }
        );



        // =====================================================
        // PAID
        // =====================================================

        
            
            
        var paid =
    numberValue(
        $('#paid').val(),
        0
    );

$('#paid').val(paid);



        // =====================================================
        // DELIVERY
        // =====================================================

        var delivery =
            numberValue(

                $('#delivery')
                    .val(),

                0
            );


        $('#delivery')
            .val(
                delivery
            );



        // =====================================================
        // DEBUG
        // =====================================================

        console.log(
            '================================='
        );


        console.log(
            'PURCHASE FORM SUBMIT'
        );


        console.log(
            $('#purchase_add_form')
                .serializeArray()
        );


        console.log(
            '================================='
        );



        // =====================================================
        // BUTTON
        // =====================================================

        $button
            .prop(
                'disabled',
                true
            )
            .html(

                '<i class="fa fa-spinner fa-spin"></i> ' +

                (
                    window.easyPurchaseLabels.saving ||
                    'Saving...'
                )

            );



        // =====================================================
        // DIRECT NATIVE SUBMIT
        //
        // IMPORTANT:
        //
        // Bypass:
        //   jQuery submit handlers
        //   validation class
        //   preventDefault()
        //   old submit plugins
        // =====================================================

        try {


            HTMLFormElement
                .prototype
                .submit
                .call(
                    form
                );


        } catch (error) {


            console.error(
                'Purchase submit error:',
                error
            );


            $button
                .prop(
                    'disabled',
                    false
                )
                .html(

                    '<i class="fa fa-save"></i> ' +

                    (
                        window.easyPurchaseLabels.savePurchase ||
                        'Save Purchase'
                    )

                );


            bootbox.alert(

                'Submit Error: ' +
                error.message

            );
        }


        return false;
    }



    // =========================================================
    // DOCUMENT READY
    // =========================================================

    $(function () {


        normalizeUnitMap();



        // =====================================================
        // CLEAR AFTER SUCCESS
        // =====================================================

        if (
            localStorage.getItem(
                'remove_spo'
            )
        ) {


            localRemove(
                'spoitems'
            );


            localStorage.removeItem(
                'remove_spo'
            );
        }



        // =====================================================
        // LOAD SAVED ITEMS
        // =====================================================

        hydrateStoredItems(
            function () {


                loadItems();


                setupAutocomplete();


                $('#add_item')
                    .focus();

            }
        );



        // =====================================================
        // DATE MASK
        // =====================================================

        if ($.fn.inputmask) {


            $('#date')
                .inputmask(

                    'yyyy-mm-dd hh:mm',

                    {
                        placeholder:
                            'yyyy-mm-dd hh:mm'
                    }

                );
        }



        // =====================================================
        // ITEM CHANGE
        // =====================================================

        $(document)
            .on(

                'change keyup',

                '.purchase-qty, .secondary-purchase-qty, .purchase-unit, .purchase-cost, .transportation',

                function () {


                    updateItemFromRow(

                        $(this)
                            .closest(
                                '.purchase-item-row'
                            )

                    );

                }

            );



        // =====================================================
        // DELETE ITEM
        // =====================================================

        $(document)
            .on(

                'click',

                '.spodel',

                function () {


                    var $row =
                        $(this)
                            .closest(
                                '.purchase-item-row'
                            );


                    var itemKey =
                        String(

                            $row.data(
                                'item-key'
                            )

                        );


                    delete spoitems[
                        itemKey
                    ];


                    persistItems();


                    loadItems();

                }

            );



        // =====================================================
        // RESET
        // =====================================================

        $('#reset')
            .off(
                'click.easyPurchase'
            )
            .on(

                'click.easyPurchase',

                function () {


                    var message =

                        window.lang &&
                        window.lang.r_u_sure

                            ? window.lang.r_u_sure

                            : window.easyPurchaseLabels
                                .resetConfirm;


                    bootbox.confirm(

                        message,

                        function (
                            confirmed
                        ) {


                            if (!confirmed) {

                                return;
                            }


                            localRemove(
                                'spoitems'
                            );


                            spoitems =
                                {};


                            loadItems();


                            $('#add_item')
                                .focus();

                        }

                    );

                }

            );



        // =====================================================
        // IMPORTANT
        // REMOVE ANY SUBMIT EVENT FROM THIS SCRIPT
        // =====================================================

        $('#purchase_add_form')
            .off(
                'submit.easyPurchase'
            );



        // =====================================================
        // SAVE BUTTON
        // =====================================================

        $('#save_purchase_btn')
            .off(
                'click.easyPurchase'
            )
            .on(

                'click.easyPurchase',

                function (event) {


                    event.preventDefault();


                    event.stopPropagation();


                    if (
                        typeof event.stopImmediatePropagation ===
                        'function'
                    ) {

                        event.stopImmediatePropagation();
                    }


                    savePurchase();


                    return false;
                }

            );

    });



    // =========================================================
    // GLOBAL FUNCTIONS
    // =========================================================

    window.add_order_item =
        addOrderItem;


    window.loadItems =
        loadItems;


    window.calculateGrandTotal =
        calculateGrandTotal;


    window.fetchProductUnitData =
        fetchProductUnitData;


})(jQuery);
