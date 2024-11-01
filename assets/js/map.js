/**
 * Square Overlay : Popup on top of the markers
 *
 * @param center
 * @param width
 * @param height
 * @param color
 * @param storeName
 * @param storeDescription
 * @constructor
 */
function TBMarker(center, data, onclick) {
	this._center = center;
	this._width = 186;
	this._height = 45;
	this._data = data;
	this._drew = false;
}

// Inherit baidu map's overlay
TBMarker.prototype = new BMap.Overlay();


TBMarker.prototype.initialize = function (map) {

	this._map = map;
	var thisOverlay = this;

	var div = document.createElement("div");

	div.className += ' marker-overlay';
	if (this._data.bgcolor) {
		div.style.backgroundColor = this._data.bgcolor;
		div.style.borderColor = this._data.bgcolor;
	}
	if (this._data.fgcolor) div.style.color = this._data.fgcolor;

    var parser = new DOMParser;
	var markerName = document.createElement("span");
	markerName.className += ' marker-name';
    var dom = parser.parseFromString(
        '<!doctype html><body>' + this._data.name,
        'text/html');
    var tmp = dom.body.textContent;
	markerName.innerHTML = tmp;
	var markerDescription = document.createElement("span");
	markerDescription.className += ' marker-description';
    dom = parser.parseFromString(
        '<!doctype html><body>' + this._data.description,
        'text/html');
    tmp = dom.body.textContent;
	markerDescription.innerHTML = tmp;

	div.appendChild(markerName);
	div.appendChild(markerDescription);

	map.getPanes().markerPane.appendChild(div);
	this._div = div;

	jQuery(div).click(function () {
		thisOverlay.toggle();
	});

	this._data.marker.addEventListener('click', function () {
		thisOverlay.toggle();
	});

	return div;
}

TBMarker.prototype.draw = function () {
	var position = this._map.pointToOverlayPixel(this._center);
	this._div.style.left = position.x - this._width / 2  - 3 + "px";
	this._div.style.top = position.y - 2*this._height + 3 + "px";
	this._div.style.height = "auto";
	this._div.style.opacity = 0.9;

	if (!this._drew) {
		if (this._data.isHidden) {
			this._data.marker.show();
			this.hide();
		} else {
			this.show()
			this._data.marker.hide();
		}
		this._drew = true;
	}


}

TBMarker.prototype.show = function () {
	if (this._div) {
		this._div.style.display = "";
		this._data.marker.hide();
	    var position = this._map.pointToOverlayPixel(this._center);
        this._div.style.top = position.y - this._div.offsetHeight - 10 + "px";
		// As in iPhone, this overlay cannot be click to hide, so add this automatic silly way
		if (this._data.isHidden) {
    		var delay = this._data.showtime;
    		(function(marker){
			setTimeout(function () {
			    marker.hide();
			}, delay);
        })(this);
	    }
	}
}

TBMarker.prototype.hide = function () {
	if (this._div) {
		this._div.style.display = "none";
		this._data.marker.show();
	}
}

TBMarker.prototype.toggle = function () {
	if (this._div) {
		if (this._div.style.display == "") {
			this.hide();
		}
		else {
			this.show();
		}
	}
}
