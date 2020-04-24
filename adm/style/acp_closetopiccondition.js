(function ($) {    // Avoid conflicts with other libraries
    $().ready(function () {
        enmCondition={
            NotExists:0,
            Exists:1
        }
        function Init()
        {
            //changeVisible($('#forum_type_limitpostsintopic'), 'blockOptions');
        }
        $("#forumsearch").autocomplete_ls(
            {
                url: U_CLOSETOPICCONDITION_PATH_FORUM,
                sortResults: false,
                width: 800,
                maxItemsToShow: 20,
                selectFirst: true,
                fixedPos:false,
                minChars: 1,
                showResult: function (value, data) {
                    return '<span style="">' + hilight(value, $("#forumsearch").val()) + '</span>';
                },
                onItemSelect: function (item) {
                    var cbo = $("#forum");
                    select_combo(item, cbo);
                },
            });

        $("#submit_select_forum").on("click", function(e)
        {
            e.preventDefault();
            $("#blockCloseTopic").hide();
            $("#submit_select_forum").hide();
            $("#loader_get").css('display', 'inline-block');
            //console.log($( "#forum option:selected" ).text());
            var path = U_CLOSETOPICCONDITION_PATH_GET;
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: "forum_id=" + $("#forum").val() + "&forum_name=" + $( "#forum option:selected" ).text(),
                url: path,
                success: function (data) {
                    get_forum_options(data)
                }
            });      
            
            $("#usersearch").autocomplete_ls(
            {
                url: U_CLOSETOPICCONDITION_PATH_USER,
                sortResults: false,
                width: 600,
                maxItemsToShow: 20,
                selectFirst: true,
                fixedPos:false,
                minChars: 1,
                showResult: function (value, data) {
                    return '<span style="">' + hilight(value, $("#usersearch").val()) + '</span>';
                },
                onItemSelect: function (item) {
                    //goto_user(item);
//                    console.log(item);
                },
    
            });
            $("#sender_search").autocomplete_ls(
            {
                url: U_CLOSETOPICCONDITION_PATH_USER,
                sortResults: false,
                width: 600,
                maxItemsToShow: 20,
                selectFirst: true,
                fixedPos:false,
                minChars: 1,
                showResult: function (value, data) {
                    return '<span style="">' + hilight(value, $("#sender_search").val()) + '</span>';
                },
                onItemSelect: function (item) {
                    //goto_user(item);
//                    console.log(item);
                },
    
            });

        });


        $('#forum_type_limitpostsintopic').on('change', function () {
                    changeVisible(this, 'blockOptions');
                });
        $("#limitposts_number").on('change', function () {
             changeEnableByLimitPostsNumber(this, "blockCloseTopicOptions");
        });
        $("input[name=chkConditions").on('change', function () {
             var selectedValue = $(this).val();
           if(parseInt(selectedValue) == 0)
                $("#blockCloseTopicOptions").hide();
            else
                $("#blockCloseTopicOptions").show();
        });


        $("#chkBlockLimitPost").on('change', function () {
             changeEnable(this, "blockLimitPost");
        });
        $("#chkBlockTopicPeriodInactive").on('change', function () {
             changeEnable(this, "blockTopicPeriodInactive");
        });

        $("#chkLastPost").on('change', function () {
             changeEnable(this, "blockLastPost");
        });
//        $("#chkNotySend").on('change', function () {
//             changeEnable(this, "blockNotySend");
//        });
        $("#btnSend").on('click', function (e) {
            e.preventDefault();
            //$("#btnSend").hide(); //debug
            $("#loader_save").css('display', 'inline-block');

            var limitposts_number = $("#limitposts_number").val();
            var path = U_CLOSETOPICCONDITION_PATH_SAVE;
           data_to_send = $("#acp_closetopiccondition").serialize();
           //alert(data_to_send);
//           console.log(data_to_send);
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data_to_send,
                url: path,
                success: function (data) {
                    //alert('success');
                    console.log(data);
                    $("#btnSend").show();
                    $("#loader_save").hide();
                    output_info_new(data.MESSAGE, 'warning');
                }
            });
        });
        $("#btnSavePeriod").on('click', function (e) {
            e.preventDefault();
            $("#loader_save").css('display', 'inline-block');

            var path = U_CLOSETOPICCONDITION_PATH_PERIOD;
           data_to_send = $("#acp_closetopiccondition_common").serialize();
//           console.log(data_to_send);
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data_to_send,
                url: path,
                success: function (data) {
                    //alert('success');
                      $("#closetopiccondition_period").find("option[value='" + data.LIMITPOSTS_PERIOD + "']").attr("selected","selected");

                    $("#btnSavePeriod").show();
                    $("#loader_save").hide();
                    output_info_new(data.MESSAGE, 'warning');
                }
            });
        });

        function get_forum_options(data)
        {
		console.log(data);
		$("#blockCloseTopic").show();
		 $("#submit_select_forum").show();
		 $("#loader_get").hide();

		 if(parseInt(data.IS_CONDITIONS_EXISTS) == 0)
			 $("#blockCloseTopicOptions").hide();
		 else
			 $("#blockCloseTopicOptions").show();
		$("input[name=chkConditions][value=" + data.IS_CONDITIONS_EXISTS + "]").prop('checked', true);
		$("#spForumName").text(data.FORUM_NAME);

		$("#chkBlockLimitPost").prop('checked', parseInt(data.LIMITPOSTS_NUMBER )> 0);
		changeEnable($("#chkBlockLimitPost"), "blockLimitPost");
		$("#limitposts_number").val(data.LIMITPOSTS_NUMBER);

		$("#chkBlockTopicPeriodInactive").prop('checked', parseInt(data.LIMITTIME_PERIOD )> 0);
		changeEnable($("#chkBlockTopicPeriodInactive"), "blockTopicPeriodInactive");
		$("#limittime_period").val(data.LIMITTIME_PERIOD);

		$("input[name=chkCloseOnlyNormalTopics][value=" + data.CLOSE_ONLY_NORMAL_TOPICS + "]").prop('checked', true);
		$("input[name=chkCloseByEachCondition][value=" + data.CLOSE_BY_EACH_CONDITION + "]").prop('checked', true);
		$("#usersearch").val(data.LASTPOSTER_NAME);
		$("#chkLastPost").prop('checked', data.IS_LAST_POST);
		$("#txtLastPost").html(data.LAST_MESSAGE );

		$("#chkNotySend").prop('checked', data.IS_NOTY_SEND);
		$("#txtTopicPoster").val(data.TOPICPOSTER_MESSAGE);
		$("#txtModerators").val(data.MODERATOR_MESSAGE);
		$("#sender_search").val(data.NOTY_SENDER_NAME);

		$("#chkBlockTopicPeriodInactive").prop('checked', parseInt(data.LIMITTIME_PERIOD )> 0);
		changeEnable($("#chkBlockTopicPeriodInactive"), "blockTopicPeriodInactive");
		changeEnable($("#chkLastPost"), "blockLastPost");
		changeEnable($("#chkNotySend"), "blockNotySend");
        }

        function changeVisible(el, idDiv) {
            var div = $('#' + idDiv);
            if ($(el).prop('checked')) {
               $(div).show();
           }
            else {
               $(div).hide();
            }
        }
        function changeEnable(el, idDiv) {
            var div = $('#' + idDiv);
            if ($(el).prop('checked')) {

                $(div).children().prop('disabled', false);
                $(div).find('dl').css('opacity', '1');
            }
            else {
                $(div).children().prop('disabled', true);
                $(div).find('dl').css('opacity', '0.3');
            }
        }
        function changeEnableByLimitPostsNumber(el, idDiv) {
            var div = $('#' + idDiv);
            if (parseInt( $(el).val()) > 0 ){

                $(div).children().prop('disabled', false);
                $(div).find('dl').css('opacity', '1');
                changeEnable($("#chkLastPost"), "blockLastPost");
                changeEnable($("#chkNotySend"), "blockNotySend");
            }
            else {
                $(div).children().prop('disabled', true);
                $(div).find('dl').css('opacity', '0.3');
            }
        }
       function changeEnableByCondition(idDiv, selectedValue) {
            var div = $('#' + idDiv);
            if (parseInt( selectedValue) > 0 ){

                $(div).children().prop('disabled', false);
                $(div).find('dl').css('opacity', '1');
                changeEnable($("#chkLastPost"), "blockLastPost");
                changeEnable($("#chkNotySend"), "blockNotySend");
            }
            else {
                $(div).children().prop('disabled', true);
                $(div).find('dl').css('opacity', '0.3')
            }
        }
        $("#submit").on("click", function (e) {
            e.preventDefault();

            $(this).hide();
//            $("#loader").show();
//            $("#groupname_list option").prop("selected", "selected");
//            $("#username_list option").prop("selected", "selected");
            data_to_send = $("#postform").serialize();
            var path = U_CLOSETOPICCONDITION_PATH_NOTY;
            $.ajax({
                type: 'POST',
                dataType: 'json',
                data: data_to_send,
                url: path,
                success: function (data) {
                    send_notification(data);
                }
            });

        });



    });

    function select_combo(item, cbo)
    {
       $(cbo).find("option[value='" + item.data[0] + "']").attr("selected","selected");
    }
        function hilight(value, term) {
            return value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + term.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
        }

    //creates a new jQuery UI notification message
    function output_info_new(message, type, expire, is_reload) {
        if (type == null) type = 'notification';
        if (expire == null) expire = 4000;
        var n = noty({
            text: message,
            type: type,
            timeout: expire,
            layout: 'topRight',
            theme: 'defaultTheme',
            callback: {
                afterClose: function () {
                    if (is_reload == null || is_reload == '' || is_reload != true) return;
                    window.location.reload();
                }
            }
        });
    }
})(jQuery);                                                        // Avoid conflicts with other libraries
