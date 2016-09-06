var zones_loaded = false;
var OA_output = new Array();

var _order = {
  head   : {},
  right  : {},
  left   : {},
  normal : OA_zones,
  tail   : {}
};

var dynamic_zones = {
  'zone_75'   : 75,
  'zone_76'   : 76,
  'zone_77'   : 77,
  'zone_78'   : 78,
  'zone_79'   : 79,
  'zone_80'   : 80,
  'zone_233'  : 233,
  'zone_242'  : 242,
  'zone_243'  : 243,
  'zone_244'  : 244,
  'zone_245'  : 245,
  'zone_246'  : 246,
  'zone_247'  : 247,
  'zone_248'  : 248,
  'zone_249'  : 249,
  'zone_250'  : 250,
  'zone_251'  : 251,
  'zone_303'  : 303,
  'zone_304'  : 304,
  'zone_305'  : 305,
  'zone_306'  : 306,
  'zone_307'  : 307,
  'zone_332'  : 332
};
OA_zones = $.extend({}, OA_zones, dynamic_zones);

var OA_zones_count = new Object();
if ('undefined' == typeof OA_remap) OA_remap = {};

function OA_show() {}

function mesto_bind(id, key, group)
{
  if (OA_zones_count[id]) {
    key && !OA_zones[key] && OA_zones_count[id]++;
  }
  else {
    OA_zones_count[id] = 1;
  }

  if (!key) {
    key = 'zone_' + id + '-' + OA_zones_count[id];
  }
  group = (_order[group] && group) || 'normal';
  if (!zones_loaded) {
    _order[group][key] = id;
    OA_zones = $.extend({}, _order['head'], _order['right'], _order['left'], _order['normal'], _order['tail']);
    document.writeln('<div class="mesto-banner" id="mesto-' + key + '"></div>');
  }
  else {
    put_banner(key);
  }
}

function OA_zone_remap(key)
{
  $.each(OA_remap || [], function(s_id, t_id) {
    var regex = new RegExp('^(zone_)' + s_id + '(-[0-9]+)?$');
    key = key.replace(regex, '$1' + t_id + '$2');
  });

  return key;
}

(function() {
  if (OA_remap) {
    var _remapped = {};
    $.each(OA_zones, function(key) {
      key = OA_zone_remap(key);
      _remapped[key] = key.replace(/^zone_([0-9]+)(-[0-9]+)?$/, '$1');
    });
    OA_zones = _remapped;
  }

  $('.index-adv-teaser a').live('click', function (){
    $.get('/hit_banner');
  });
})();

function put_banner(key) {
  var oa_key = OA_zone_remap(key);
  $('#mesto-' + key + ', #mesto-' + oa_key).replaceWith(OA_output[oa_key]);
}

function show_zones() {
  zones_loaded = true;
  $.each(OA_zones, function(key){
      put_banner(key);
  });
};
