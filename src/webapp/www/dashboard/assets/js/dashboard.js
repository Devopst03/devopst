$(document).ready(function(){
    var iframSrc = $('#dashboard-iframe').attr("src");
    var queryString = iframSrc.split(/[?#]/)[0];

    $('#product').prop('selectedIndex',0);
    $('#env').prop('selectedIndex',0);
    $('#service').prop('selectedIndex',0);
    var dashboard;

    $('#product').change(function() {
        var product = $('#product option:selected').val();
        var currentAttr = $(this).attr('id');
        dashboard = queryString+'?type='+currentAttr;
        if(product != '') {
            dashboard += "&product="+product;
        }
        $('#dashboard-iframe').attr( 'src', dashboard);
        $.ajax({
            type: "POST",
            url: "inventory.php",
            data: {product: product,type: currentAttr},
            success: function (entityOptions) {
                $('#env').html(entityOptions);
            },
        });

    });

    $('#env').change(function() {
        var product = $('#product option:selected').val();
        var env = $('#env option:selected').val();
        var currentAttr = $(this).attr('id');
        dashboard = queryString+'?type='+currentAttr;

        if(product != '') {
            dashboard += "&product="+product;
        }
        if (env != '') {
            dashboard += "&env="+env;
        }
        $('#dashboard-iframe').attr( 'src', dashboard);
        $.ajax({
            type: "POST",
            url: "inventory.php",
            data: {product: product, env: env, type: currentAttr},
             success: function (entityOptions) {
                $('#service').html(entityOptions);
            },
        });

    });


    $('#service').change(function() {
        var product = $('#product option:selected').val();
        var env = $('#env option:selected').val();
        var service = $('#service option:selected').val();
        var currentAttr = $(this).attr('id');

        dashboard = queryString+'?type='+currentAttr;

        if(product != '') {
            dashboard += "&product="+product;
        }
        if (env != '') {
            dashboard += "&env="+env;
        }
        if (service != '') {
            dashboard += "&service="+service;
        }
        $('#dashboard-iframe').attr( 'src', dashboard);
    });
});

