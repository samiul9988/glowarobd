$(document).ready(function () {

    var updateOutput = function () {
        console.log(JSON.stringify($('#nestable').nestable('serialize')));
        $('#nestable-output').val(JSON.stringify($('#nestable').nestable('serialize')));
    };

    $('#nestable').nestable().on('change', updateOutput);

    updateOutput();

    $("#add-item").submit(function (e) {
        e.preventDefault();
        id = Date.now();
        var label = $("#add-item > [name='name']").val();
        var url = $("#add-item > [name='url']").val();
        var icon = $("input[name='changeIcon']").val();
        var background_color = $("#add-item > .menu_background_color > [name='background_color']").val();
        var background_color_status = $("#add-item > .aiz-switch > [name='background_color_status']").val();
        var background_font_color = $("#add-item > .menu_background_color > [name='background_font_color']").val();
        if(background_color_status == 'on'){
            var checked_val = 'checked';
        }else{
            var checked_val = '';
        }
        if ((url == "") || (label == "")) return;
        var item =
            '<li class="dd-item dd3-item" data-id="' + id + '" data-label="' + label + '" data-url="' + url + '"data-icon="' + icon + '" data-background_color="'+background_color+'" data-background_color_status="'+background_color_status+'" data-background_font_color="'+background_font_color+'">' +
            '<div class="dd-handle dd3-handle" > Drag</div>' +
            '<div class="dd3-content"><span>' + label + '</span>' +
            '<div class="item-edit">Edit</div>' +
            '</div>' +
            '<div class="item-settings d-none">' +
            '<p><label for="">Menu Background </label><br><label class="aiz-switch aiz-switch-success mb-0"><input type="hidden" name="background_color_status" value="'+background_color_status+'"><input type="checkbox" name="show_background_color_status" class="change_background" '+checked_val+'><span></span></label></p>' +
            '<div class="menu_background_color"><p><label for="">Menu Background Color<br><input type="color" name="background_color" value="' + background_color + '"></label></p>' +
            '<p><label for="">Menu Background Font Color<br><input type="color" name="background_font_color" value="' + background_font_color + '"></label></p></div>' +
            '<p><label for="">Navigation Label<br><input type="text" name="navigation_label" value="' + label + '"></label></p>' +
            '<p><label for="">Navigation Url<br><input type="text" name="navigation_url" value="' + url + '"></label></p>' +
            '<p><label for="">Navigation Icon<br><input type="text" name="navigation_icon" value="' + icon + '"></label></p>' +
            '<p><a class="item-delete" href="javascript:;">Remove</a> |' +
            '<a class="item-close" href="javascript:;">Close</a></p>' +
            '</div>' +
            '</li>';


        $("#nestable > .dd-list").append(item);
        $("#nestable").find('.dd-empty').remove();
        $("#add-item > [name='name']").val('');
        $("#add-item > [name='url']").val('');
        $("input[name='changeIcon']").val('');
        updateOutput();
    });

    $("body").delegate(".item-delete", "click", function (e) {
        $(this).closest(".dd-item").remove();
        updateOutput();
    });


    $("body").delegate(".item-edit, .item-close", "click", function (e) {
        var item_setting = $(this).closest(".dd-item").find(".item-settings");
        if (item_setting.hasClass("d-none")) {
            item_setting.removeClass("d-none");
        } else {
            item_setting.addClass("d-none");
        }
    });

    $("body").delegate("input[name='navigation_label']", "change paste keyup", function (e) {
        $(this).closest(".dd-item").data("label", $(this).val());
        // $(this).closest(".dd-item").find(".dd3-content span").text($(this).val());
        $(".label-"+$(this).data("id")).text($(this).val());

    });

    $("body").delegate("input[name='navigation_url']", "change paste keyup", function (e) {
        $(this).closest(".dd-item").data("url", $(this).val());
    });

    $("#nst_menu").mouseover(function (e) {
        $(".edit_icon_get_val").change();
    });

    $(".edit_icon_get_val").on("change",function (e) {
        $(this).closest(".pass_icon_val").data("icon", $(this).val());
    });

    // $(".change_background").on("click",function (e) {
    //     if($(this).prop('checked')){
    //         var this_value = 'on';
    //     }else{
    //         var this_value = 'off';
    //     }
    //     $(this).closest("li").attr("data-background_color_status", this_value);
    //     $('.background_color_status_val').change();
    // });

    $("body").delegate(".change_background", "click", function (e) {
        if($(this).prop('checked')){
            var this_value = 'on';
        }else{
            var this_value = 'off';
        }
        $(this).closest("li").data("background_color_status", this_value);
    });

    $("body").delegate("input[name='background_color']", "change paste keyup", function (e) {
        $(this).closest("li").data("background_color", $(this).val());
    });

    $("body").delegate("input[name='background_font_color']", "change paste keyup", function (e) {
        $(this).closest("li").data("background_font_color", $(this).val());
    });

});
