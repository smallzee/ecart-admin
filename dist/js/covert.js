$('#validate_code_form').validate({
        rules: {
            purchase_code: "required"
        }
    });
    $('#result_success , #result_fail').hide();

    $('#validate_code_form').on('submit', function(e) { 
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#validate_code_form").validate().form()) {
            var code = $('#purchase_code').val();
            var flag = validURL(domain_url);
            if(flag){
                $.ajax({
                    type: 'GET',
                    url: 'https://wrteam.in/validator/home/validator?purchase_code=' + code+ '&domain_url=' +domain_url,
                    data: formData,
                    beforeSend: function() {
                        $('#btnValidate').html('Please wait..');
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(result) {
                        if (!result.error) {
                            $('#result_success').html(result.message);
                            $('#result_success').show().delay(6000).fadeOut();
                            $('#validate_code_form')[0].reset();
                            response_data = "code_bravo="+result.purchase_code;
                            response_data += "&time_check="+result.token;
                            response_data += "&code_adam="+result.username;
                            response_data += "&dr_firestone="+result.item_id;
                            response_data += "&add_dr_gold=1";
                            $.ajax({
                                type: 'POST',
                                url: 'public/db-operation.php',
                                data: response_data,
                                beforeSend: function() {
                                    $('#btnValidate').val('Please Wait..');
                                },
                                dataType: "json",
                                success: function(result) {
                                    // alert(JSON.stringify(result));
                                    if (result['error'] == false) {
                                        $('#result_success').html(result.message);
                                        $('#result_success').show().delay(6000).fadeOut();
                                    } else {
                                        $('#result_fail').html(result.message);
                                        $('#result_fail').show().delay(6000).fadeOut();
                                        $('#btnValidate').html('Validate Now');
                                    }                        
                                }
                            });
                        } else {
                            $('#result_fail').html(result.message);
                            $('#result_fail').show().delay(6000).fadeOut();
                        }
                        $('#btnValidate').html('Validate Now');

                    }
                });

            }else{
                alert("Invalid Domain URL");
            }
            
        }
    });
    var myURL;
    function validURL(myURL) {
        var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))'+ // ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ //port
        '(\\?[;&amp;a-z\\d%_.~+=-]*)?'+ // query string
        '(\\#[-a-z\\d_]*)?$','i');
        return pattern.test(myURL);
    }