function isEmail(data) {
    var regx = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regx.test(data);
}

function isPhone(data) {
    var regx = /^\+?(?:[0-9]\x20?){6,14}[0-9]$/;
    return regx.test(data);
}

function isNumber(data) {
    var regx = /^-{0,1}\d*\.{0,1}\d+$/;
    return regx.test(data);
}

function isDate(data) {
    var sp = data.indexOf('/') ? '/' : (data.indexOf('-') ? '-' : null);
    if (!sp)
        return false;
    var parts = data.split(sp);
    if (parts.length != 3)
        return false;
    return true;
}
