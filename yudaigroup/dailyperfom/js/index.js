$(function(){

    // 比較用色付け
    $(".comparison").each(function(i, o){
        var jq_obj = $(o);
        var num = Number(jq_obj.text());
        if (num < 100 && 90 <= num) {
            jq_obj.css('color','#ff0000');
        } else if (num < 90) {
            jq_obj.css('color','#ff0000');
            jq_obj.css('background','#ffd9d9');
        }
    });

    // マイナス色付け処理
    $(".plus_minus").each(function(i, o){
        var jq_obj = $(o);
        var num = Number(jq_obj.text());
        if (num < 0) {
            jq_obj.css('color','#ff0000');
        }
    });
    // マイナスとプラスに色つけ
    $(".plus_minus_plus").each(function(i, o){
        var jq_obj = $(o);
        var num = Number(jq_obj.text());
        if (num < 0) {
            jq_obj.css('color','#ff0000');
        } else if (num > 0) {
            jq_obj.css('color','#0000ff');
        }
    });

    // %用処理
    $(".percent").each(function(i, o){
        $(o).text($(o).text()+"%");
    });

    // パーセントプラマイ
    $(".percent_plus_minus").each(function(i, o){
        var jq_obj = $(o);
        var num = Number(jq_obj.text());
        if (num < 0) {
            jq_obj.css('color','#ff0000');
        }
        $(o).text($(o).text()+"%");
    });


    /*// 小数点以下を四捨五入する
    $(".int_round").each(function(i, o){
        var num = Number($(o).text());
        $(o).text(Math.round(num));
    });*/

    // 天気の色つけ
    $(".weather").each(function(i, o){
        var jq_obj = $(o);
        var weather_text = jq_obj.text();

        if (weather_text === "晴れ") {
            jq_obj.css('color','#ff5100');
        } else if (weather_text === "雨") {
            jq_obj.css('color','#0066ff');
        }
    });


    // 上tdの予算と比較する 上なら青 下なら赤
    $(".budget_up_down").each(function(i, o){
        var jq_obj = $(o);
        var revenue = Number(jq_obj.text());
        var budget = Number($("td",jq_obj.parent().prev()).eq(jq_obj.index()).text());
        console.log('予算');
        console.log(budget);

        if (revenue && budget) {
            if (revenue < budget) {
                jq_obj.css('background','#FF7C80');
            } else {
                jq_obj.css('background','#66FFFF');
            }
        }
    });

    // 上tdの予算と比較する 上なら青 下なら赤
    $(".budget_up_down2").each(function(i, o){
        var jq_obj = $(o);
        var revenue = Number(jq_obj.text());
        var budget = Number($("td",jq_obj.parent()).eq(0).text());
        console.log('見込み');
        console.log(jq_obj.text());
        console.log('予算');
        console.log($("td",jq_obj.parent()).eq(0).text());

        if (revenue && budget) {
            if (revenue < budget) {
                jq_obj.css('background','#ffcecf');
            } else {
                jq_obj.css('background','#c8d8ff');
            }
        }
    });

    // 人時売上比較する 上なら青 下なら赤
    $(".man_hour_budget_up_down").each(function(i, o){
        var jq_obj = $(o);
        var man_hour = Number(jq_obj.text());

        var man_hour_budget = Number($("td.man_hour_budget",jq_obj.parent()).text());

        if (man_hour && man_hour_budget) {
            if (man_hour < man_hour_budget) {
                jq_obj.css('background','#FF7C80');
            } else {
                jq_obj.css('background','#66FFFF');
            }
        }
    });

    // 人時売上比較する 上なら青 下なら赤
    $(".man_hour_budget_up_down2").each(function(i, o){
        var jq_obj = $(o);
        var man_hour = Number(jq_obj.text());

        var man_hour_budget = Number($("td.man_hour_budget2",jq_obj.parent()).text());

        if (man_hour && man_hour_budget) {
            if (man_hour < man_hour_budget) {
                jq_obj.css('background','#FF7C80');
            } else {
                jq_obj.css('background','#66FFFF');
            }
        }
    });


    // カンマを付ける stringになるため最後に
    $(".comma").each(function(i, o){
        var num = Number($(o).text());
        $(o).text(num.toLocaleString('ja'));
    });



});