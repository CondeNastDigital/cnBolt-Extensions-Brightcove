var brightcoveCmsRoot = null; // Due to an unknown race condition, Bolt.conf("paths.root") is not available during our init :(

function initBrightcoveField(fieldId, rootPath){

    brightcoveCmsRoot = rootPath;

    // Initialize Autocomplete
    $( ".brightcove-"+fieldId+" input" ).autocomplete({
        source: brightcoveCmsRoot+"brightcove/search",
        minLength: 3,
        select: function( event, ui ) {
            $("#"+fieldId).val(ui.item.id);
            var preview = brightcovePreview(ui.item, false).addClass("clearfix");
            $(".brightcove-"+fieldId+" .current").html(preview);
            $(".brightcove-"+fieldId+" .current-wrapper").show();
            $("#"+fieldId).data("item", ui.item);
        },
        search: function(event, ui) {
            $('.brightcove-'+fieldId+' .spinner').show();
        },
        response: function(event, ui) {
            $('.brightcove-'+fieldId+' .spinner').hide();
        }
    }).data( "ui-autocomplete")._renderItem = function( ul, item ) {
        return brightcovePreview(item, true).appendTo( ul );
    };

    // Preload already selected video
    var bcId = $("#"+fieldId).val();
    if(bcId)
        brightcovePreload(fieldId, bcId);
}

/**
 * Preload the connected video information from Brightcove
 * @param bcid
 */
function brightcovePreload(fieldId, bcid){
    $.ajax({
            method: "GET",
            url: brightcoveCmsRoot+"brightcove/search",
            dataType: "json",
            data: { term: bcid }
        })
        .success(function(data) {
            // Video object found
            if($.isArray(data) && data.length > 0) {
                item = data[0];
                $("#"+fieldId).data("item", item);

                var preview = brightcovePreview(item, false).addClass("clearfix");
                $(".brightcove-"+fieldId+" .current").html(preview);

                $(".brightcove-"+fieldId+" .current").removeClass("error");
                $(".brightcove-"+fieldId+" .current-wrapper").show();
                $(".brightcove-"+fieldId+" button").show();
            }
            // Video object not found
            else {
                $(".brightcove-"+fieldId+" .current").html("Video not found. Please search for a new one.");

                $(".brightcove-"+fieldId+" .current").addClass("error");
                $(".brightcove-"+fieldId+" .current-wrapper").show();
                $(".brightcove-"+fieldId+" button").hide();
            }
        })
        .error(function(){
            $(".brightcove-"+fieldId+" .current").html("Error while contacting Brighcove");

            $(".brightcove-"+fieldId+" .current").addClass("error");
            $(".brightcove-"+fieldId+" .current-wrapper").show();
            $(".brightcove-"+fieldId+" button").hide();
        })
}

/**
 * Generate small preview div from a video item
 * @param item
 * @param linked
 * @returns {*|jQuery}
 */
function brightcovePreview(item, linked){
    return $( "<li class='brightcove-preview'>" )
        .append( (linked ? "<a>" : "")+"<div class='thumbnail' style='background-image:url(" + item.images.thumbnail.src + ")'></div>" + item.name.substr(0,60) + "<br/><sub>" + item.id + " - " + item.updated_at + "</sub>"+(linked ? "</a>" : ""));
}

/**
 * Import data from brightcove video item to mapped input/textarea fields
 * @param fieldId
 * @param map
 */
function brightcoveImport(fieldId, map){

    var item = $("#"+fieldId).data("item");

    // Copy text data
    if("text" in map) {
        for (var key in map["text"]) {
            var value = brightcoveGetPath(item, map["text"][key]);
            $("[name=" + key + "]").val(value); // TODO: Does not update ckeditor managed text areas
        }
    }
    // Copy and import image data
    if("images" in map) {
        for (var key in map["images"]) {
            var url = brightcoveGetPath(item, map["images"][key]);

            if(url){
                $.ajax({
                        method: "POST",
                        url: brightcoveCmsRoot+"brightcove/importImage",
                        dataType: "json",
                        data: { url: url, bcid: item.id }
                    })
                    .success(function(data) {
                        if(data.path != undefined) {
                            $("[name=" + key + "\\[file\\]]").val(data.path);
                            var preview = brightcoveCmsRoot+"thumbs/200x150c/"+data.path;
                            $("#thumbnail-" + key + " img").attr("src", preview);
                        }
                    });
            }
        }
    }
}

/**
 * get an element from a mulit-level array by string path
 * See http://stackoverflow.com/questions/8817394/javascript-get-deep-value-from-object-by-passing-path-to-it-as-string
 * @param obj
 * @param path
 * @returns {*}
 */
function brightcoveGetPath(obj, path){
    for (var i=0, path=path.split('.'), len=path.length; i<len; i++){
        if ($.isArray( obj[path[i]])){
            obj = obj[path[i]][ path[i+1]];
            i++;
        }else{
            obj = obj[path[i]];
        }
    }
    return obj;
}