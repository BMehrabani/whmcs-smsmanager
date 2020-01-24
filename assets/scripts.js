$(document).ready(function () {
    var remaining = $('#remaining'),
            messages = $('#lmessages');
    var charset7bit = ['@', 'A£', '$', 'A¥', '?¨', '?©', '?¹', '?¬', '?²', '?‡', "\n", '??', '?¸', "\r", '?…', '?¥', 'I”', '_', 'I¦', 'I“', 'I›', 'I©', 'I ', 'I¨', 'I£', 'I?', 'I?', '?†', '?¦', '??', '?‰', ' ', '!', '"', '#', 'A¤', '%', '&', "'", '(', ')', '*', '+', ',', '-', '.', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?', 'A?', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '?„', '?–', '?‘', '?œ', 'A§', 'A?', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '?¤', '?¶', '?±', '?¼', '? '];
    var charset7bitext = ["\f", '^', '{', '}', '\\', '[', '~', ']', '|', 'â‚¬'];
    $('#textbox1').keyup(function () {
        var content = $('#textbox1').val();
        var chars_arr = content.split('');
        var coding = '7bit';
        var chars_sms = 160;
        for (i = 0; i < chars_arr.length; i++) {
            if (charset7bit.indexOf(chars_arr[i]) >= 0) {
                chars_sms = '160';
            } else if (charset7bitext.indexOf(chars_arr[i]) >= 0) {
                chars_sms = '160';
            } else {
                chars_sms = '70';
                chars_used = chars_arr.length;
                break;
            }
        }
        var chars = this.value.length,
                messages = Math.ceil(chars / chars_sms),
                remaining = messages * chars_sms - (chars % (messages * chars_sms) || messages * chars_sms);
        if (messages == 0) {
            messages = 1;
        }
        if(remaining == 0){
            remaining = 160;
        }
        $('#remaining').text(remaining);
        $('#lmessages').text(messages);
    });
});