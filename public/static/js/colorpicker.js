//https://juejin.cn/post/6888655752085372941

class Colorpicker {
    constructor(id, rgbdiv) {
        this.div = document.getElementById(id);
        this.rgbdiv = document.getElementById(rgbdiv);
        this.rgb = [0, 0, 0];
    }

    init() {
        load_colorpicker(this.div);
        this.H = this.div.querySelector('#H');
        this.HRect = this.H.querySelector('rect');
        this.HRect1 = this.H.querySelector('rect:last-child');
        this.HSlide = this.H.querySelector('#slide1')
        this.HFlag = false;
        this.SV = this.div.querySelector('#SV');
        this.SVRect = this.SV.querySelector('rect:last-child');
        this.SVSlide = this.SV.querySelector('#slide2');
        this.SVFlag = false;
        this.endColors = this.div.querySelectorAll('#endColor');
        this.Hval = 0;
        this.Sval = 0;
        this.Vval = 0;
        this.SVRect.addEventListener('mousedown', () => {
            this.SVFlag = true;
        })
        this.SVRect.addEventListener('mousemove', ev => {
            if(!this.SVFlag) return;
            this.Sval = ev.offsetX / this.SV.offsetWidth * 100;
            this.Vval = (1 - ev.offsetY / this.SV.offsetHeight) * 100;
            this.SVSlide.style.left = ev.offsetX + 26 + 'px';
            this.SVSlide.style.top = ev.offsetY + 85 + 'px';
            this.setHSV();
        })
        this.SVRect.addEventListener('mouseup', () => {
            this.SVFlag = false;
        })
        this.HRect.addEventListener('mousedown', () => {
            this.HFlag = true;
        })
        this.HRect.addEventListener('mousemove', ev => {
            if(!this.HFlag) return;
            let offsetY = ev.offsetY / this.H.offsetHeight;
            this.HSlide.style.top = ev.offsetY - 8 + 'px'
            this.Hval = 360 * offsetY;
            this.setHSV();
        })
        this.HRect.addEventListener('mouseup', () => {
            this.HFlag = false;
        })
        this.setHSV();
    }

    setHSV(){
        let StopColor = `
    rgb(${hsvtorgb(this.Hval, 100, 100).join(',')})
  `;
        [...this.endColors].map(el => el.setAttribute('stop-color', StopColor));
        this.SV.style.background = StopColor;
        this.rgb = hsvtorgb(this.Hval, this.Sval, this.Vval);
        this.rgb16 = rgbto16(this.rgb);
        this.rgbdiv.innerHTML = "RGB(" + this.rgb + ") or " + this.rgb16;
    }
}

function load_colorpicker(div) {
    div.innerHTML = '\
    <div id="color-picker">\
        <div id="HSV">\
            <div id="SV">\
                <svg>\
                <defs>\
                    <linearGradient id="gradient-black" x1="0%" y1="100%" x2="0%" y2="0%">\
                        <stop offset="0%" stop-color="#000000" stop-opacity="1"></stop>\
                        <!-- endColor -->\
                        <stop id="endColor" offset="100%" stop-color="#CC9A81" stop-opacity="0"></stop>\
                    </linearGradient>\
                    <linearGradient id="gradient-white" x1="0%" y1="100%" x2="100%" y2="100%">\
                        <stop offset="0%" stop-color="#FFFFFF" stop-opacity="1"></stop>\
                        <!-- endColor -->\
                        <stop id="endColor" offset="100%" stop-color="#CC9A81" stop-opacity="0"></stop>\
                    </linearGradient>\
                </defs>\
                <rect x="0" y="0" width="100%" height="100%" fill="url(#gradient-white)"></rect>\
                <rect x="0" y="0" width="100%" height="100%" fill="url(#gradient-black)"></rect>\
                </svg>\
                <div id="slide2"></div>\
            </div>\
            <div id="H">\
              <svg>\
                <defs>\
                  <linearGradient id="gradient-hsv" x1="0%" y1="100%" x2="0%" y2="0%">\
                    <stop offset="0%" stop-color="#FF0000" stop-opacity="1"></stop>\
                    <stop offset="13%" stop-color="#FF00FF" stop-opacity="1"></stop>\
                    <stop offset="25%" stop-color="#8000FF" stop-opacity="1"></stop>\
                    <stop offset="38%" stop-color="#0040FF" stop-opacity="1"></stop>\
                    <stop offset="50%" stop-color="#00FFFF" stop-opacity="1"></stop>\
                    <stop offset="63%" stop-color="#00FF40" stop-opacity="1"></stop>\
                    <stop offset="75%" stop-color="#0BED00" stop-opacity="1"></stop>\
                    <stop offset="88%" stop-color="#FFFF00" stop-opacity="1"></stop>\
                    <stop offset="100%" stop-color="#FF0000" stop-opacity="1"></stop>\
                  </linearGradient>\
                </defs>\
                <rect x="0" y="0" width="100%" height="100%" fill="url(#gradient-hsv)"></rect>\
              </svg>\
              <div id="slide1"></div>\
            </div>\
        </div>\
    </div>';
}
function hsvtorgb(h, s, v) {
    s = s / 100;
    v = v / 100;
    var h1 = Math.floor(h / 60) % 6;
    var f = h / 60 - h1;
    var p = v * (1 - s);
    var q = v * (1 - f * s);
    var t = v * (1 - (1 - f) * s);
    var r, g, b;
    switch (h1) {
        case 0:
            r = v;
            g = t;
            b = p;
            break;
        case 1:
            r = q;
            g = v;
            b = p;
            break;
        case 2:
            r = p;
            g = v;
            b = t;
            break;
        case 3:
            r = p;
            g = q;
            b = v;
            break;
        case 4:
            r = t;
            g = p;
            b = v;
            break;
        case 5:
            r = v;
            g = p;
            b = q;
            break;
    }
    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

function rgbto16(rgbarr) {
    let strHex = "#";
    for (let i = 0; i < rgbarr.length; i++) {
        let hex = Number(rgbarr[i]).toString(16);
        if (hex === "0") {
            hex += hex;
        } else if (hex.length === 1) {
            hex = "0" + hex;
        }
        strHex += hex;
    }
    return strHex;
}