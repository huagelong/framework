/**
 * Created by wangkaihui on 16/10/20.
 */

var $ = require('jquery');
var alert = require('modules/alert');

module.exports = function(selector) {
    $(selector).on('click', function() {
        alert('~Duang 的一下，我就弹出来了。');
        return false;
    });
};