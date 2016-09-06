/**
 * Created by JetBrains PhpStorm.
 * User: barba
 * Date: 24.02.11
 * Time: 11:23
 * To change this template use File | Settings | File Templates.
 */

var metro = {
  regionNames: ['ВАО','ЗАО','САО','СВАО','СЗАО','ЦАО','ЮАО','ЮВАО','ЮЗАО'],
  regions: {
    0: [],
    1: [],
    2: [],
    3: [],
    4: [],
    5: [],
    6: [],
    7: [],
    8: []
  },
  selectedStations: {
    0: 0,
    1: 0,
    2: 0,
    3: 0,
    4: 0,
    5: 0,
    6: 0,
    7: 0,
    8: 0
  },

  lines: {
    1: {
      name: 'Сокольническая',
      stations: [
        {id: 40, name: 'Улица Подбельского', coords: [496, 0, 594, 8]},
        {id: 39, name: 'Черкизовская', coords: [495, 10, 565, 19]},
        {id: 38, name: 'Преображенская площадь', coords: [496, 20, 589, 28]},
        {id: 37, name: 'Сокольники', coords: [495, 40, 557, 49]},
        {id: 36, name: 'Красносельская', coords: [496, 50, 574, 58]},
        {id: 35, name: 'Комсомольская', coords: [391, 208, 470, 219]},
        {id: 34, name: 'Красные Ворота', coords: [378, 222, 456, 231]},
        {id: 33, name: 'Чистые Пруды', coords: [359, 240, 427, 249]},
        {id: 32, name: 'Лубянка', coords: [289, 271, 339, 280]},
        {id: 31, name: 'Охотный Ряд', coords: [240, 305, 303, 316]},
        {id: 30, name: 'Библиотека им. Ленина', coords: [240, 359, 346, 367]},
        {id: 29, name: 'Кропоткинская', coords: [215, 385, 290, 394]},
        {id: 28, name: 'Парк Культуры', coords: [138, 396, 210, 409]},
        {id: 27, name: 'Фрунзенская', coords: [034, 555, 101, 562]},
        {id: 26, name: 'Спортивная', coords: [040, 565, 102, 573]},
        {id: 157, name: 'Воробъевы Горы', coords: [024, 574, 101, 583]},
        {id: 25, name: 'Университет', coords: [040, 585, 102, 593]},
        {id: 24, name: 'Проспект Вернадского', coords: [19, 595, 101, 604]},
        {id: 23, name: 'Юго-Западная', coords: [031, 612, 104, 625]}
      ]
    },
    2: {name: 'Замоскворецкая', stations: [
        {id: 1, name: 'Речной вокзал', coords: [133, 10, 207, 21]},
        {id: 2, name: 'Водный Стадион', coords: [127, 20, 207, 29]},
        {id: 3, name: 'Войковская', coords: [142, 31, 207, 39]},
        {id: 4, name: 'Сокол', coords: [172, 40, 204, 49]},
        {id: 5, name: 'Аэропорт', coords: [157, 50, 205, 57]},
        {id: 6, name: 'Динамо', coords: [164, 61, 205, 69]},
        {id: 7, name: 'Белорусская', coords: [169, 186, 242, 201]},
        {id: 8, name: 'Маяковская', coords: [235, 215, 300, 224]},
        {id: 9, name: 'Тверская', coords: [232, 240, 290, 255]},
        {id: 10, name: 'Театральная', coords: [248, 314, 315, 326]},
        {id: 11, name: 'Новокузнецкая', coords: [338, 339, 404, 360]},
        {id: 12, name: 'Павелецкая', coords: [390, 400, 451, 411]},
        {id: 13, name: 'Автозаводская', coords: [396, 441, 471, 451]},
        {id: 14, name: 'Коломенская', coords: [397, 451, 466, 458]},
        {id: 15, name: 'Каширская', coords: [394, 461, 454, 472]},
        {id: 18, name: 'Кантемировская', coords: [323, 574, 404, 583]},
        {id: 19, name: 'Царицыно', coords: [353, 585, 405, 594]},
        {id: 20, name: 'Орехово', coords: [355, 595, 404, 604]},
        {id: 21, name: 'Домодедовская', coords: [331, 605, 404, 614]},
        {id: 22, name: 'Красногвардейская', coords: [309, 620, 403, 630]}
    ]},
    3: {name: 'Арбатско-Покровская', stations: [
        {id: 41, name: 'Щелковская', coords: [515, 108, 580, 118]},
        {id: 42, name: 'Первомайская', coords: [515, 119, 588, 127]},
        {id: 43, name: 'Измайловская', coords: [515, 129, 585, 136]},
        {id: 44, name: 'Партизанская', coords: [515, 139, 585, 148]},
        {id: 45, name: 'Семеновская', coords: [515, 159, 579, 167]},
        {id: 46, name: 'Электрозаводская', coords: [515, 169, 606, 176]},
        {id: 47, name: 'Бауманская', coords: [515, 179, 581, 187]},
        {id: 48, name: 'Курская', coords: [425, 265, 474, 276]},
        {id: 49, name: 'Площадь Революции', coords: [313, 324, 415, 331]},
        {id: 159, name: 'Арбатская', coords: [188, 369, 243, 376]},
        {id: 160, name: 'Смоленская', coords: [127, 342, 187, 350]},
        {id: 52, name: 'Киевская', coords: [161, 318, 196, 337]},
        {id: 171, name: 'Парк Победы', coords: [053, 326, 119, 336]},
        {id: 170, name: 'Мякининская', coords: [023, 157, 92, 165]},
        {id: 169, name: 'Славянский бульвар', coords: [015, 283, 77, 298]},
        {id: 168, name: 'Строгино', coords: [023, 167, 69, 175]},
        {id: 167, name: 'Волокаламская', coords: [022, 147, 104, 155]},
        {id: 166, name: 'Митино', coords: [023, 137, 57, 144]},
        {id: 62, name: 'Крылатское', coords: [023, 177, 85, 185]},
        {id: 61, name: 'Молодежная', coords: [023, 187, 88, 195]},
        {id: 60, name: 'Кунцевская', coords: [021, 210, 87, 222]}
      ]},

    4: {name: 'Филевская', stations: [
        {id: 60, name: 'Кунцевская', coords: [021, 210, 87, 222]},
        {id: 59, name: 'Пионерская', coords: [037, 227, 99, 235]},
        {id: 58, name: 'Филевский Парк', coords: [48, 237, 126, 245]},
        {id: 57, name: 'Багратионовская', coords: [58, 247, 145, 255]},
        {id: 56, name: 'Фили', coords: [67, 256, 99, 265]},
        {id: 55, name: 'Кутузовская', coords: [78, 267, 144, 275]},
        {id: 54, name: 'Студенческая', coords: [87, 277, 157, 285]},
        {id: 52, name: 'Киевская', coords: [161, 318, 196, 337]},
        {id: 161, name: 'Смоленская(Филевская)', coords: [197, 328, 258, 336]},
        {id: 158, name: 'Арбатская(Филевская)', coords: [212, 343, 267, 351]},
        {id: 53, name: 'Александровский Сад', coords: [132, 357, 237, 367]},
        {id: 163, name: 'Международная', coords: [134, 287, 214, 296]},
        {id: 162, name: 'Выставочная', coords: [134, 298, 205, 306]}
      ]},
    5: {name: 'Кольцевая', stations: [
        {id: 48, name: 'Курская', coords: [425, 265, 474, 276]},
        {id: 35, name: 'Комсомольская', coords: [391, 208, 470, 219]},
        {id: 136, name: 'Проспект Мира', coords: [357, 183, 432, 192]},
        {id: 134, name: 'Новослободская', coords: [193, 173, 281, 184]},
        {id: 7, name: 'Белорусская', coords: [169, 186, 242, 201]},
        {id: 133, name: 'Краснопресненская', coords: [107, 219, 203, 228]},
        {id: 52, name: 'Киевская', coords: [161, 318, 196, 337]},
        {id: 28, name: 'Парк Культуры', coords: [138, 396, 210, 409]},
        {id: 100, name: 'Октябрьская', coords: [190, 429, 257, 439]},
        {id: 132, name: 'Добрынинская', coords: [294, 435, 370, 445]},
        {id: 12, name: 'Павелецкая', coords: [390, 400, 451, 411]},
        {id: 75, name: 'Таганская', coords: [419, 358, 475, 368]}
      ]},
    6: {name: 'Калужско-Рижская', stations: [
        {id: 89, name: 'Новоясеневская', coords: [125, 615, 208, 624]},
        {id: 90, name: 'Ясенево', coords: [160, 605, 205, 613]},
        {id: 91, name: 'Теплый Стан', coords: [145, 595, 205, 603]},
        {id: 92, name: 'Коньково', coords: [156, 585, 205, 595]},
        {id: 93, name: 'Беляево', coords: [162, 575, 205, 585]},
        {id: 94, name: 'Калужская', coords: [149, 565, 205, 575]},
        {id: 95, name: 'Новые Черемушки', coords: [123, 545, 206, 554]},
        {id: 96, name: 'Профсоюзная', coords: [138, 535, 205, 543]},
        {id: 97, name: 'Академическая', coords: [128, 525, 205, 533]},
        {id: 98, name: 'Ленинский проспект', coords: [134, 505, 205, 514]},
        {id: 99, name: 'Шаболовская', coords: [135, 495, 205, 504]},
        {id: 100, name: 'Октябрьская', coords: [190, 429, 257, 439]},
        {id: 88, name: 'Третьяковская', coords: [262, 350, 337, 358]},
        {id: 74, name: 'Китай-город', coords: [360, 298, 425, 308]},
        {id: 103, name: 'Тургеневская', coords: [357, 257, 428, 264]},
        {id: 102, name: 'Сухаревская', coords: [303, 209, 367, 217]},
        {id: 136, name: 'Проспект Мира', coords: [357, 183, 432, 192]},
        {id: 104, name: 'Рижская', coords: [395, 071, 442, 080]},
        {id: 105, name: 'Алексеевская', coords: [395, 60, 464, 70]},
        {id: 106, name: 'ВДНХ', coords: [395, 50, 432, 60]},
        {id: 107, name: 'Ботанический Сад', coords: [395, 40, 478, 50]},
        {id: 108, name: 'Свиблово', coords: [395, 30, 444, 40]},
        {id: 109, name: 'Бабушкинская', coords: [395, 020, 466, 030]},
        {id: 110, name: 'Медведково', coords: [395, 10, 457, 20]}
      ]},
    7: {name: 'Таганско-Краснопресненская', stations: [
        {id: 63, name: 'Планерная', coords: [132, 89, 191, 97]},
        {id: 64, name: 'Сходненская', coords: [123, 99, 191, 107]},
        {id: 65, name: 'Тушинская', coords: [134, 109, 191, 117]},
        {id: 66, name: 'Щукинская', coords: [129, 119, 191, 127]},
        {id: 67, name: 'Октябрьское поле', coords: [103, 129, 191, 137]},
        {id: 68, name: 'Полежаевская', coords: [118, 139, 191, 147]},
        {id: 69, name: 'Беговая', coords: [146, 149, 191, 157]},
        {id: 70, name: 'Улица 1905 года', coords: [115, 159, 191, 167]},
        {id: 71, name: 'Баррикадная', coords: [125, 208, 194, 217]},
        {id: 72, name: 'Пушкинская', coords: [174, 252, 237, 263]},
        {id: 73, name: 'Кузнецкий Мост', coords: [254, 262, 331, 270]},
        {id: 74, name: 'Китай-Город', coords: []},
        {id: 75, name: 'Таганская', coords: [419, 358, 475, 368]},
        {id: 76, name: 'Пролетарская', coords: [460, 400, 538, 410]},
        {id: 77, name: 'Волгоградский Пр-т', coords: [495, 555, 589, 565]},
        {id: 78, name: 'Текстильщики', coords: [495, 575, 565, 583]},
        {id: 79, name: 'Кузьминки', coords: [495, 585, 550, 593]},
        {id: 80, name: 'Рязанский Пр-т', coords: [495, 595, 595, 605]},
        {id: 81, name: 'Выхино', coords: [495, 615, 540, 624]}
//        Underkonstruction {id: 165, name: 'Пронская', coords: [495, 625, 550, 632]},
      ]},
    8: {name: 'Калининская', stations: [
        {id: 82, name: 'Новогиреево', coords: [515, 219, 576, 227]},
        {id: 83, name: 'Перово', coords: [515, 229, 555, 237]},
        {id: 84, name: 'Шоссе Энтузиастов', coords: [515, 239, 605, 248]},
        {id: 85, name: 'Авиамоторная', coords: [515, 259, 588, 267]},
        {id: 86, name: 'Площадь Ильича', coords: [460, 310, 548, 322]},
        {id: 87, name: 'Марксистская', coords: [422, 345, 500, 355]},
        {id: 88, name: 'Третьяковская', coords: [262, 350, 337, 358]}
//        Underkonstruction {id: 173, name: 'Новокосино', coords: [515, 209, 573, 217]}
      ]},
    9: {name: 'Серпуховско-Тимирязевская', stations: [
        {id: 172, name: 'Бульвар Дмитрия Донского', coords: [238, 640, 308, 657]},
        {id: 156, name: 'Аннино', coords: [263, 624, 304, 633]},
        {id: 155, name: 'Улица Академика Янгеля', coords: [242, 605, 304, 613]},
        {id: 130, name: 'Пражская', coords: [250, 595, 304, 603]},
        {id: 129, name: 'Южная', coords: [265, 585, 304, 593]},
        {id: 128, name: 'Чертановская', coords: [235, 575, 304, 583]},
        {id: 127, name: 'Севастопольская', coords: [220, 542, 304, 552]},
        {id: 126, name: 'Нахимовский пр-т', coords: [221, 527, 304, 539]},
        {id: 125, name: 'Нагорная', coords: [254, 517, 304, 525]},
        {id: 124, name: 'Нагатинская', coords: [239, 507, 304, 515]},
        {id: 123, name: 'Тульская', coords: [257, 497, 304, 505]},
        {id: 122, name: 'Серпуховская', coords: [294, 447, 378, 458]},
        {id: 121, name: 'Полянка', coords: [296, 419, 345, 427]},
        {id: 120, name: 'Боровицкая', coords: [247, 368, 310, 378]},
        {id: 119, name: 'Чеховская', coords: [241, 253, 300, 261]},
        {id: 118, name: 'Цветной бульвар', coords: [252, 190, 300, 207]},
        {id: 117, name: 'Менделеевская', coords: [204, 162, 281, 170]},
        {id: 116, name: 'Савеловская', coords: [216, 139, 280, 147]},
        {id: 115, name: 'Дмитровская', coords: [212, 129, 280, 137]},
        {id: 114, name: 'Тимирязевская', coords: [205, 119, 280, 127]},
        {id: 113, name: 'Петровско-Разумовская', coords: [239, 79, 305, 96]},
        {id: 112, name: 'Владыкино', coords: [322, 40, 379, 48]},
        {id: 111, name: 'Отрадное', coords: [322, 30, 375, 38]},
        {id: 131, name: 'Бибирево', coords: [322, 20, 372, 28]},
        {id: 135, name: 'Алтуфьево', coords: [322, 10, 374, 20]}
      ]},
    10: {name: 'Люблинская', stations: [
        {id: 175, name: 'Трубная', coords: [302, 192, 348, 202]},
        {id: 174, name: 'Сретенский Бульвар', coords: [369, 246, 460, 255]},
        {id: 137, name: 'Чкаловская', coords: [427, 278, 493, 287]},
        {id: 138, name: 'Римская', coords: [461, 324, 512, 332]},
        {id: 139, name: 'Крестьянская Застава', coords: [460, 387, 572, 396]},
        {id: 140, name: 'Дубровка', coords: [413, 525, 464, 533]},
        {id: 144, name: 'Кожуховская', coords: [413, 535, 485, 543]},
        {id: 141, name: 'Печатники', coords: [413, 545, 466, 553]},
        {id: 142, name: 'Волжская', coords: [413, 555, 463, 563]},
        {id: 143, name: 'Люблино', coords: [413, 565, 458, 573]},
        {id: 145, name: 'Братиславская', coords: [413, 575, 489, 583]},
        {id: 146, name: 'Марьино', coords: [413, 585, 460, 593]}

//        {id: 177, name: 'Марьина роща', coords: [322, 139, 403, 146]}
//        {id: 176, name: 'Достоевская', coords: [320, 159, 398, 170]}
//        {id: 246, name: 'Площадь Суворова', coords: [321, 172, 422, 181]}

      ]},
    11: {name: 'Каховская линия', stations: [
      {id: 17, name: 'Каховская', coords: [307, 542, 366, 552]},
      {id: 164, name: 'Варшавская', coords: [371, 487, 432, 494]},
      {id: 15, name: 'Каширская', coords: [394, 461, 454, 472]}
    ]},
    12: {name: 'Бутовская', stations: [
        {id: 182, name: 'Бунинская Аллея', coords: [295, 705, 381, 713]},
        {id: 181, name: 'Улица Горчакова', coords: [295, 695, 378, 703]},
        {id: 180, name: 'Бульвар Адм. Ушакова', coords: [295, 685, 403, 693]},
        {id: 179, name: 'Улица Скобелевская', coords: [295, 675, 395, 683]},
        {id: 178, name: 'Улица Старокачаловская', coords: [295, 658, 415, 667]}
      ]}
  },
  selected: {
    1: [],
    2: [],
    3: [],
    4: [],
    5: [],
    6: [],
    7: [],
    8: [],
    9: [],
    10: [],
    11: [],
    12: []
  },

  getStationName: function(id, lineId) {
    for (var i = this.lines[lineId].stations.length - 1; i >= 0; i--) {
      if (this.lines[lineId].stations[i].id == id) return this.lines[lineId].stations[i].name;
    }
  },
  //перезаписываем массив станций метро
  extendLines: function(newLines){
    if (newLines) {
      this.lines = newLines;
    }
  },

  element: {
    opt: 'metro-list',
    add: function(id, lineId, stname) {
      $('input[value=м. '+stname+']').attr('checked','checked');
    },
    remove: function(id, stname) {
      $('input[value=м. '+stname+']').removeAttr('checked');
    },
    addLine: function(id, name) {
      var li = document.createElement("li");
      li.id = 'line-li' + id;
      li.className = 'line-' + id;
      li.innerHTML = '<a href="#" onclick="metro.checkLine('+ id +'); return false;"><span>' + name + '</span></a>';
      document.getElementById(this.opt).appendChild(li);

    },
    removeLine: function(id) {
      var lit = document.getElementById('line-li' + id);
      if (lit) document.getElementById(this.opt).removeChild(lit);

    }
  },

  checkLine: function(elem, stArr) {
    var arr, lineId;

    if (elem.constructor == Number) {
      arr = metro.lines[elem].stations;
      lineId = elem;
    } else

    switch (elem.id)
    {
      case "m1":
        arr = metro.lines['1'].stations;
        lineId = 1;
        break;

      case "m2":
        arr = metro.lines['2'].stations;
        lineId = 2;
        break;

      case "m3":
        arr = metro.lines['3'].stations;
        lineId = 3;
        break;

      case "m4":
        arr = metro.lines['4'].stations;
        lineId = 4;
        break;

      case "m5":
        arr = metro.lines['5'].stations;
        lineId = 5;
        break;

      case "m6":
        arr = metro.lines['6'].stations;
        lineId = 6;
        break;

      case "m7":
        arr = metro.lines['7'].stations;
        lineId = 7;
        break;

      case "m8":
        arr = metro.lines['8'].stations;
        lineId = 8;
        break;

      case "m9":
        arr = metro.lines['9'].stations;
        lineId = 9;
        break;

      case "m10":
        arr = metro.lines['10'].stations
        lineId = 10;
        break;

      case "m11":
        arr = metro.lines['11'].stations;
        lineId = 11;
        break;
      case "m12":
        arr = metro.lines['12'].stations;
        lineId = 12;
        break;
    }

    if (elem.className == '') {

      elem.className = 'checked';
      document.getElementById("metroimage").className='op';

     //записали все станции линии в выбранные
     this.selected[lineId] = [];
      for (var i = arr.length - 1; i >= 0 ; i--) {
        if (arr[i]) {
          this.selected[lineId].push(arr[i].id);
          this.element.remove(arr[i].id);
          document.getElementById("pp"+arr[i].id).style.display = 'block';
        }
      }

      //выделяем саму линию и легенду

      document.getElementById('pp' + (parseInt(lineId) + 1000)).style.display = 'block';
      document.getElementById('pp' + (parseInt(lineId) + 2000)).style.display = 'block';

      //добавляем название линии в список выбраных станций
      this.element.addLine(lineId, this.lines[lineId].name);

    } else {

      elem.className = '';

      //удалии все станции линии из выбранных | перпесывание выбранных
      this.selected[lineId] = stArr || [];

      //выделяем саму линию и легенду
      document.getElementById('pp' + (parseInt(lineId) + 1000)).style.display = 'none';
      document.getElementById('pp' + (parseInt(lineId) + 2000)).style.display = 'none';

      //удаляем название линии из списока выбранных станций
      this.element.removeLine(lineId);

      if (!stArr) {
        for (var i = arr.length - 1; i >= 0 ; i--) {
          if (arr[i]) document.getElementById("pp"+arr[i].id).style.display = 'none';
        }
      } else {
        for (var i = stArr.length - 1; i >= 0 ; i--) {
          if (stArr[i]) this.element.add(stArr[i], lineId, this._getStationName(stArr[i]));
        }
      }

      if (this._isClear()) document.getElementById("metroimage").className='op-no';
    }

    $('#stations').val(this._joinSelected());
  },

  checkStation: function(id) {
    var elem = document.getElementById('pp' + id);
    var showLines = [];
    var lineId, name;


    if (elem.style.display == 'none') {

      elem.style.display = 'block';
      document.getElementById("metroimage").className='op';

      //заносим станцию в список выбранных
      for (var i = this._getSize(this.lines); i > 0; i--)
        for (var j = this.lines[i].stations.length - 1; j >= 0; j--) {

          if (this.lines[i].stations[j] && this.lines[i].stations[j].id == id) {
            lineId = i;
            name = this.lines[i].stations[j].name;
            this.selected[i].push(id);
          }
        }

      //если есть линии, выбранные целиком, заносим в список название линии, иначе - название станции

      if (showLines.length > 0) {
        for(i = showLines.length - 1; i >= 0; i--)
          this.checkLine(document.getElementById('m' + showLines[i]));
      } else {
        this.element.add(id,lineId,name);
      }
      RegionsSelector.appendNodeToList($('div.metromapbox').find('input[value="м. ' + name + '"]').attr('id'), 'metro');

    } else {//удаление станции
      var removeLines = [],
          lid,
          current_line_id;

      elem.style.display = 'none';

      RegionsSelector.removeNodeFromList('metro_' + id);

      //удаляем станцию из выбранных и смотрим, не была ли эта линия выбрана целиком
      for (i = this._getSize(this.selected); i > 0; i--) {
        this.selected[i] = $.map(this.selected[i], function(el, ind) {if (el != id) return el; else lid = i});
        if (lid && this.selected[i].length + 1 == this.lines[i].stations.length) removeLines.push(i);
        if (lid) current_line_id = lid;
        lid = undefined;
      }

      //если удалили станцию из выбранной целиком линии, надо удалить
      //выделение линии и поменять ее название на список станций
      if (removeLines.length > 0) {
        for(i = removeLines.length - 1; i >= 0; i--)
          this.checkLine(document.getElementById('m' + removeLines[i]), this.selected[removeLines[i]] )
      } else {

        this.element.remove(id, this.getStationName(id, current_line_id));
      }

      if (this._isClear()) document.getElementById("metroimage").className='op-no';
    }

    $('#stations').val(this._joinSelected());


  },

   _getSize: function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
  },

  _getStationName: function(id) {
    for (var i = this._getSize(this.lines); i > 0; i--)
        for (var j = this.lines[i].stations.length - 1; j >= 0; j--)
          if (this.lines[i].stations[j].id == id)
            return this.lines[i].stations[j].name;

    return false;
  },

  _isClear: function(){
    var s = 0;
    for (var i = this._getSize(this.selected); i > 0; i--)
      s += this.selected[i].length;

    return !s;
  },

  _joinSelected: function(){
    var key, str = [];
    for (key in this.selected) {
      if (this.selected.hasOwnProperty(key)) {
        for (var i = this.selected[key].length - 1; i >= 0; i--) {
          if (!findInString(str,this.selected[key][i]))
            str.push(this.selected[key][i]);
        }
      }
    }
    str = str.join(',');
    return str;
  },
  clickStation: function(name, action) {
    var station_name = $.trim(name.substr(3));
    if (typeof action !== 'undefined') {
      if (action === true) {
        if ($('div.metromapbox').find('input[value='+name+']').attr('checked') === false)
          $('area[title='+station_name+']').trigger('click');
      } else if (action === false){
        if ($('div.metromapbox').find('input[value='+name+']').attr('checked') === true)
          $('area[title='+station_name+']').trigger('click');
      }
    }
    else {
      $('area[title='+station_name+']').trigger('click');
    }
  },
  _checkRegionSelection: function(action, name) {
    var station = 'м. '+name;
    //определяем id округа
    var reg_id = 0;
    $.each(metro.regions, function(i, v){
      var index = $.inArray(station, v);
      if (index > -1) reg_id = i;
    });
    //добавляем или убираем количество выделенных станций округа
    if (action) {
      metro.selectedStations[reg_id] += 1;
    } else {
      metro.selectedStations[reg_id] -= 1;
    }

    //проверяем все или не все станции округа выделены и выделяем или убираем выделение с округа

    if (metro.selectedStations[reg_id] < metro.regions[reg_id].length) {
      $('input[value="'+metro.regionNames[reg_id]+'"]').removeAttr('checked');
    } else {
      $('input[value="'+metro.regionNames[reg_id]+'"]').attr('checked','checked');
    }

  },

  /**
   * получает станции метро с привязкой к округам и заносит их в this.regions
   */
  getStationsToRegions: function(regions){
    $.each(regions, function(i, v) {
      $.get(
          '/form/district'
          , {district: v}
          , function(st) {
            $.each(st, function(j, item) {
              metro.regions[i].push(item);
            })
          }, 'json'
      );
    });
  },

  /**
   * отмечаем на карте станции, которые есть в списке
   */
  setMetroStations: function() {
    var list = $('#stations').attr('value').split(',');

    if (list != '') {
      for (var i = list.length - 1; i >= 0; i--)
        metro.checkStation(parseInt(list[i]));
    }
  },

  /**
   * выбор станции кликом по чекбоксу
   */
  bindCheckBoxClick: function(regions){
    $('#metro-list').find('input').bind('click', function() {
      if (typeof regions == 'undefined') {
        var reg_id = -1;
      } else {
        reg_id = $.inArray(this.value, regions);
      }
      var action = this.checked;
      if (reg_id !== -1) {
        $.each(metro.regions[reg_id], function(i, v) {
          metro.clickStation(v, action);
        });
        if (action)
          metro.selectedStations[reg_id] = metro.regions[reg_id].length;
        else
          metro.selectedStations[reg_id] = 0;

        this.checked = action;
      }
      else {
        var station = this.value;
        metro.clickStation(station);
      }

    });
  },

  setInputIds: function(){
    var that = this;


    $.each(that.lines, function(){
      $.each(this.stations, function(){
        var $input = $('#metro-list').find('input[value="м. '+this.name+'"]');
        if ($input.length) {
          $input.attr('id', 'metro_' + this.id);
        }
      });
    });
  }
};






var lines = {
    1: {
      name: 'Сокольническая',
      stations: [
        {id: 40, name: 'Улица Подбельского', coords: [496, 0, 594, 8]},
        {id: 39, name: 'Черкизовская', coords: [495, 10, 565, 19]},
        {id: 38, name: 'Преображенская площадь', coords: [496, 20, 589, 28]},
        {id: 37, name: 'Сокольники', coords: [495, 40, 557, 49]},
        {id: 36, name: 'Красносельская', coords: [496, 50, 574, 58]},
        {id: 35, name: 'Комсомольская', coords: [391, 208, 470, 219]},
        {id: 34, name: 'Красные ворота', coords: [378, 222, 456, 231]},
        {id: 33, name: 'Чистые пруды', coords: [369, 246, 460, 255]},
        {id: 32, name: 'Лубянка', coords: [289, 271, 339, 280]},
        {id: 31, name: 'Охотный ряд', coords: [240, 305, 303, 316]},
        {id: 30, name: 'Библиотека им. Ленина', coords: [240, 359, 346, 367]},
        {id: 29, name: 'Кропоткинская', coords: [215, 385, 290, 394]},
        {id: 28, name: 'Парк Культуры', coords: [138, 396, 210, 409]},
        {id: 27, name: 'Фрунзенская', coords: [034, 555, 101, 562]},
        {id: 26, name: 'Спортивная', coords: [040, 565, 102, 573]},
        {id: 157, name: 'Воробъевы Горы', coords: [024, 574, 101, 583]},
        {id: 25, name: 'Университет', coords: [040, 585, 102, 593]},
        {id: 24, name: 'Проспект Вернадского', coords: [19, 595, 101, 604]},
        {id: 23, name: 'Юго-Западная', coords: [031, 605, 104, 615]}
      ]
    },
    2: {name: 'Замоскворецкая', stations: [
        {id: 1, name: 'Речной вокзал', coords: [133, 10, 207, 21]},
        {id: 2, name: 'Водный Стадион', coords: [127, 20, 207, 29]},
        {id: 3, name: 'Войковская', coords: [142, 31, 207, 39]},
        {id: 4, name: 'Сокол', coords: [172, 40, 204, 49]},
        {id: 5, name: 'Аэропорт', coords: [157, 50, 205, 57]},
        {id: 6, name: 'Динамо', coords: [164, 61, 205, 69]},
        {id: 7, name: 'Белорусская', coords: [169, 186, 242, 201]},
        {id: 8, name: 'Маяковская', coords: [235, 215, 300, 224]},
        {id: 9, name: 'Тверская', coords: [232, 240, 290, 255]},
        {id: 10, name: 'Театральная', coords: [248, 314, 315, 326]},
        {id: 11, name: 'Новокузнецкая', coords: [338, 339, 412, 360]},
        {id: 12, name: 'Павелецкая', coords: [390, 400, 455, 411]},
        {id: 13, name: 'Автозаводская', coords: [396, 441, 477, 451]},
        {id: 14, name: 'Коломенская', coords: [397, 451, 466, 458]},
        {id: 15, name: 'Каширская', coords: [394, 461, 460, 472]},
        {id: 18, name: 'Кантемировская', coords: [323, 574, 404, 583]},
        {id: 19, name: 'Царицыно', coords: [353, 585, 405, 594]},
        {id: 20, name: 'Орехово', coords: [355, 595, 404, 604]},
        {id: 21, name: 'Домодедовская', coords: [324, 605, 404, 614]},
        {id: 22, name: 'Красногвардейская', coords: [309, 620, 403, 630]}
    ]},
    3: {name: 'Арбатско-Покровская', stations: [
        {id: 41, name: 'Щелковская', coords: [515, 108, 580, 118]},
        {id: 42, name: 'Первомайская', coords: [515, 119, 588, 127]},
        {id: 43, name: 'Измайловская', coords: [515, 129, 585, 136]},
        {id: 44, name: 'Партизанская', coords: [515, 139, 585, 148]},
        {id: 45, name: 'Семеновская', coords: [515, 159, 579, 167]},
        {id: 46, name: 'Электрозаводская', coords: [515, 169, 606, 176]},
        {id: 47, name: 'Бауманская', coords: [515, 179, 581, 187]},
        {id: 48, name: 'Курская', coords: [425, 265, 479, 276]},
        {id: 49, name: 'Площадь Революции', coords: [313, 324, 415, 331]},
        {id: 159, name: 'Арбатская', coords: [188, 369, 243, 376]},
        {id: 160, name: 'Смоленская', coords: [127, 342, 187, 350]},
        {id: 52, name: 'Киевская', coords: [161, 318, 196, 337]},
        {id: 171, name: 'Парк Победы', coords: [053, 326, 119, 336]},
        {id: 170, name: 'Мякининская', coords: [023, 157, 92, 165]},
        {id: 169, name: 'Славянский бульвар', coords: [015, 283, 77, 298]},
        {id: 168, name: 'Строгино', coords: [023, 167, 69, 175]},
        {id: 167, name: 'Волокаламская', coords: [022, 147, 104, 155]},
        {id: 166, name: 'Митино', coords: [023, 137, 57, 144]},
        {id: 62, name: 'Крылатское', coords: [023, 177, 85, 185]},
        {id: 61, name: 'Молодежная', coords: [023, 187, 88, 195]},
        {id: 60, name: 'Кунцевская', coords: [021, 210, 87, 222]}
      ]},

    4: {name: 'Филевская', stations: [
        {id: 60, name: 'Кунцевская', coords: [021, 210, 87, 222]},
        {id: 59, name: 'Пионерская', coords: [037, 227, 99, 235]},
        {id: 58, name: 'Филевский Парк', coords: [48, 237, 126, 245]},
        {id: 57, name: 'Багратионовская', coords: [58, 247, 145, 255]},
        {id: 56, name: 'Фили', coords: [67, 256, 99, 265]},
        {id: 55, name: 'Кутузовская', coords: [78, 267, 144, 275]},
        {id: 54, name: 'Студенческая', coords: [87, 277, 157, 285]},
        {id: 52, name: 'Киевская', coords: [161, 318, 196, 337]},
        {id: 161, name: 'Смоленская(Филевская)', coords: [197, 328, 258, 336]},
        {id: 158, name: 'Арбатская(Филевская)', coords: [212, 343, 267, 351]},
        {id: 53, name: 'Александровский Сад', coords: [132, 357, 237, 367]},
        {id: 163, name: 'Международная', coords: [134, 287, 214, 296]},
        {id: 162, name: 'Выставочная', coords: [134, 298, 205, 306]}
      ]},
    5: {name: 'Кольцевая', stations: [
        {id: 48, name: 'Курская', coords: [425, 265, 479, 276]},
        {id: 35, name: 'Комсомольская', coords: [391, 208, 470, 219]},
        {id: 136, name: 'Проспект Мира', coords: [357, 183, 429, 192]},
        {id: 134, name: 'Новослободская', coords: [193, 173, 281, 184]},
        {id: 7, name: 'Белорусская', coords: [169, 186, 242, 201]},
        {id: 133, name: 'Краснопресненская', coords: [107, 219, 203, 228]},
        {id: 52, name: 'Киевская', coords: [161, 318, 196, 337]},
        {id: 28, name: 'Парк Культуры', coords: [138, 396, 210, 409]},
        {id: 100, name: 'Октябрьская', coords: [190, 429, 257, 439]},
        {id: 132, name: 'Добрынинская', coords: [294, 435, 370, 445]},
        {id: 12, name: 'Павелецкая', coords: [390, 400, 455, 411]},
        {id: 75, name: 'Таганская', coords: [419, 358, 481, 368]}
      ]},
    6: {name: 'Калужско-Рижская', stations: [
        {id: 89, name: 'Новоясеневская', coords: [114, 615, 208, 624]},
        {id: 90, name: 'Ясенево', coords: [160, 605, 205, 613]},
        {id: 91, name: 'Теплый Стан', coords: [145, 595, 205, 603]},
        {id: 92, name: 'Коньково', coords: [156, 585, 205, 595]},
        {id: 93, name: 'Беляево', coords: [162, 575, 205, 585]},
        {id: 94, name: 'Калужская', coords: [149, 565, 205, 575]},
        {id: 95, name: 'Новые Черемушки', coords: [123, 545, 206, 554]},
        {id: 96, name: 'Профсоюзная', coords: [138, 535, 205, 543]},
        {id: 97, name: 'Академическая', coords: [128, 525, 205, 533]},
        {id: 98, name: 'Ленинский проспект', coords: [134, 505, 205, 514]},
        {id: 99, name: 'Шаболовская', coords: [135, 495, 205, 504]},
        {id: 100, name: 'Октябрьская', coords: [190, 429, 257, 439]},
        {id: 88, name: 'Третьяковская', coords: [262, 350, 337, 358]},
        {id: 74, name: 'Китай-город', coords: [360, 298, 425, 308]},
        {id: 103, name: 'Тургеневская', coords: [357, 257, 428, 264]},
        {id: 102, name: 'Сухаревская', coords: [303, 209, 367, 217]},
        {id: 136, name: 'Проспект Мира', coords: [357, 183, 429, 192]},
        {id: 104, name: 'Рижская', coords: [395, 71, 448, 80]},
        {id: 105, name: 'Алексеевская', coords: [395, 60, 464, 70]},
        {id: 106, name: 'ВДНХ', coords: [395, 50, 432, 60]},
        {id: 107, name: 'Ботанический сад', coords: [395, 40, 478, 50]},
        {id: 108, name: 'Свиблово', coords: [395, 30, 444, 40]},
        {id: 109, name: 'Бабушкинская', coords: [395, 20, 473, 30]},
        {id: 110, name: 'Медведково', coords: [395, 10, 457, 20]}
      ]},
    7: {name: 'Таганско-Краснопресненская', stations: [
        {id: 63, name: 'Планерная', coords: [132, 89, 191, 97]},
        {id: 64, name: 'Сходненская', coords: [123, 99, 191, 107]},
        {id: 65, name: 'Тушинская', coords: [134, 109, 191, 117]},
        {id: 66, name: 'Щукинская', coords: [129, 119, 191, 127]},
        {id: 67, name: 'Октябрьское поле', coords: [103, 129, 191, 137]},
        {id: 68, name: 'Полежаевская', coords: [118, 139, 191, 147]},
        {id: 69, name: 'Беговая', coords: [146, 149, 191, 157]},
        {id: 70, name: 'Улица 1905 года', coords: [115, 159, 191, 167]},
        {id: 71, name: 'Баррикадная', coords: [125, 208, 194, 217]},
        {id: 72, name: 'Пушкинская', coords: [174, 252, 237, 263]},
        {id: 73, name: 'Кузнецкий мост', coords: [254, 262, 331, 270]},
        {id: 74, name: 'Китай-город', coords: []},
        {id: 75, name: 'Таганская', coords: [419, 358, 481, 368]},
        {id: 76, name: 'Пролетарская', coords: [460, 400, 538, 410]},
        {id: 77, name: 'Волгоградский проспект', coords: [495, 555, 589, 565]},
        {id: 78, name: 'Текстильщики', coords: [495, 575, 565, 583]},
        {id: 79, name: 'Кузьминки', coords: [495, 585, 550, 593]},
        {id: 80, name: 'Рязанский Пр-т', coords: [495, 595, 595, 605]},
        {id: 81, name: 'Выхино', coords: [495, 615, 540, 624]}
//        Underkonstruction {id: 165, name: 'Пронская', coords: [495, 625, 550, 632]},
      ]},
    8: {name: 'Калининская', stations: [
        {id: 82, name: 'Новогиреево', coords: [515, 219, 576, 227]},
        {id: 83, name: 'Перово', coords: [515, 229, 555, 237]},
        {id: 84, name: 'Шоссе Энтузиастов', coords: [515, 239, 605, 248]},
        {id: 85, name: 'Авиамоторная', coords: [515, 259, 588, 267]},
        {id: 86, name: 'Площадь Ильича', coords: [460, 310, 548, 322]},
        {id: 87, name: 'Марксистская', coords: [422, 345, 500, 355]},
        {id: 88, name: 'Третьяковская', coords: [262, 350, 337, 358]}
//        Underkonstruction {id: 173, name: 'Новокосино', coords: [515, 209, 573, 217]}
      ]},
    9: {name: 'Серпуховско-Тимирязевская', stations: [
        {id: 172, name: 'Бульвар Дмитрия Донского', coords: [238, 640, 308, 657]},
        {id: 156, name: 'Аннино', coords: [263, 624, 304, 633]},
        {id: 155, name: 'Улица академика Янгеля', coords: [242, 605, 304, 613]},
        {id: 130, name: 'Пражская', coords: [250, 595, 304, 603]},
        {id: 129, name: 'Южная', coords: [265, 585, 304, 593]},
        {id: 128, name: 'Чертановская', coords: [235, 575, 304, 583]},
        {id: 127, name: 'Севастопольская', coords: [220, 542, 304, 552]},
        {id: 126, name: 'Нахимовский проспект', coords: [221, 527, 304, 539]},
        {id: 125, name: 'Нагорная', coords: [254, 517, 304, 525]},
        {id: 124, name: 'Нагатинская', coords: [239, 507, 304, 515]},
        {id: 123, name: 'Тульская', coords: [257, 497, 304, 505]},
        {id: 122, name: 'Серпуховская', coords: [294, 447, 378, 458]},
        {id: 121, name: 'Полянка', coords: [296, 419, 345, 427]},
        {id: 120, name: 'Боровицкая', coords: [247, 368, 310, 378]},
        {id: 119, name: 'Чеховская', coords: [241, 253, 300, 261]},
        {id: 118, name: 'Цветной бульвар', coords: [252, 190, 300, 207]},
        {id: 117, name: 'Менделеевская', coords: [204, 162, 281, 170]},
        {id: 116, name: 'Савеловская', coords: [216, 139, 280, 147]},
        {id: 115, name: 'Дмитровская', coords: [212, 129, 280, 137]},
        {id: 114, name: 'Тимирязевская', coords: [205, 119, 280, 127]},
        {id: 113, name: 'Петровско-Разумовская', coords: [239, 79, 305, 96]},
        {id: 112, name: 'Владыкино', coords: [322, 40, 379, 48]},
        {id: 111, name: 'Отрадное', coords: [322, 30, 375, 38]},
        {id: 131, name: 'Бибирево', coords: [322, 20, 372, 28]},
        {id: 135, name: 'Алтуфьево', coords: [322, 10, 374, 20]}
      ]},
    10: {name: 'Люблинская', stations: [
        {id: 175, name: 'Трубная', coords: [302, 192, 354, 202]},
        {id: 174, name: 'Сретенский бульвар', coords: [359, 240, 445, 249]},
        {id: 137, name: 'Чкаловская', coords: [427, 278, 493, 287]},
        {id: 138, name: 'Римская', coords: [461, 324, 512, 332]},
        {id: 139, name: 'Крестьянская Застава', coords: [460, 387, 572, 396]},
        {id: 140, name: 'Дубровка', coords: [413, 525, 464, 533]},
        {id: 144, name: 'Кожуховская', coords: [413, 535, 485, 543]},
        {id: 141, name: 'Печатники', coords: [413, 545, 466, 553]},
        {id: 142, name: 'Волжская', coords: [413, 555, 473, 563]},
        {id: 143, name: 'Люблино', coords: [413, 565, 458, 573]},
        {id: 145, name: 'Братиславская', coords: [413, 575, 489, 583]},
        {id: 146, name: 'Марьино', coords: [413, 585, 460, 593]}

//        {id: 177, name: 'Марьина роща', coords: [322, 139, 403, 146]}
//        {id: 176, name: 'Достоевская', coords: [320, 159, 398, 170]}
//        {id: 197, name: 'Площадь Суворова', coords: [321, 172, 422, 181]}

      ]},
    11: {name: 'Каховская линия', stations: [
      {id: 17, name: 'Каховская', coords: [307, 542, 376, 552]},
      {id: 164, name: 'Варшавская', coords: [371, 487, 443, 494]},
      {id: 15, name: 'Каширская', coords: [394, 461, 460, 472]}
    ]},
    12: {name: 'Бутовская', stations: [
        {id: 182, name: 'Бунинская аллея', coords: [295, 705, 381, 713]},
        {id: 181, name: 'Улица Горчакова', coords: [295, 695, 378, 703]},
        {id: 180, name: 'Бульвар Адмирала Ушакова', coords: [295, 685, 403, 693]},
        {id: 179, name: 'Улица Скобелевская', coords: [295, 675, 395, 683]},
        {id: 178, name: 'Улица Старокачаловская', coords: [295, 658, 415, 667]}
      ]}
  }



function findInString(str, searchStr) {
  var returnArray = false;
  for (i=0; i<str.length; i++) {
    if (typeof(searchStr) == 'function') {
      if (searchStr.test(str[i])) {
        if (!returnArray) {returnArray = []}
        returnArray.push(i);
      }
    } else {
      if (str[i]===searchStr) {
        if (!returnArray) {returnArray = []}
        returnArray.push(i);
      }
    }
  }
  return returnArray;
}


var linesSpb = {
    1: {
      name: '1',
      stations: [
        {id: 183, name: 'Девяткино', coords: [368, 31, 422, 40]},
        {id: 184, name: 'Гражданский пр-т', coords: [368, 41, 455, 50]},
        {id: 185, name: 'Академическая', coords: [368, 51, 444, 60]},
        {id: 186, name: 'Политехническая', coords: [368, 61, 451, 70]},
        {id: 187, name: 'Площадь мужества', coords: [368, 71, 459, 80]},
        {id: 188, name: 'Лесная', coords: [368, 81, 407, 90]},
        {id: 189, name: 'Выборгская', coords: [368, 91, 427, 100]},
        {id: 190, name: 'Площадь Ленина', coords: [368, 101, 448, 110]},
        {id: 191, name: 'Чернышевская', coords: [368, 142, 438, 151]},
        {id: 192, name: 'Площадь восстания', coords: [366, 182, 465, 194]},
        {id: 193, name: 'Владимирская', coords: [315, 247, 392, 257]},
        {id: 194, name: 'Пушкинская', coords: [256, 360, 312, 360, 312, 351, 309, 351, 309, 342, 318, 342, 318, 351, 313, 351, 313, 367, 256, 367]},
        {id: 195, name: 'Технологически институт - I', coords: [92, 323, 220, 323, 220, 337, 233, 337, 233, 351, 220, 351, 220, 339, 92, 339]},
        {id: 196, name: 'Балтийская', coords: [101, 373, 160, 382]},
        {id: 197, name: 'Нарвская', coords: [95, 387, 145, 396]},
        {id: 198, name: 'Кировский завод', coords: [22, 426, 107, 435]},
        {id: 199, name: 'Автово', coords: [67, 442, 107, 451]},
        {id: 200, name: 'Ленинский пр-т', coords: [33, 452, 107, 461]},
        {id: 201, name: 'Пр-т ветеранов', coords: [35, 462, 107, 471]}
      ]},
    2: {name: '2', stations: [
        {id: 202, name: 'Парнас', coords: [222, 22, 262, 31]},
        {id: 203, name: 'Пр-т просвещения', coords: [222, 32, 307, 41]},
        {id: 204, name: 'Озерки', coords: [222, 42, 261, 51]},
        {id: 205, name: 'Удельная', coords: [222, 52, 272, 61]},
        {id: 206, name: 'Пионерская', coords: [222, 62, 281, 71]},
        {id: 207, name: 'Черная речка', coords: [222, 72, 288, 81]},
        {id: 208, name: 'Петроградская', coords: [222, 116, 295, 125]},
        {id: 209, name: 'Горьковская', coords: [222, 126, 286, 135]},
        {id: 210, name: 'Невский пр-т', coords: [165, 185, 231, 194]},
        {id: 211, name: 'Сенная площадь', coords: [149, 267, 231, 276]},
        {id: 212, name: 'Технологический институт - II', coords: []},
        {id: 213, name: 'Фрунзенская', coords: [166, 402, 231, 411]},
        {id: 214, name: 'Московские ворота', coords: [139, 412, 231, 421]},
        {id: 215, name: 'Электросила', coords: [167, 422, 231, 431]},
        {id: 216, name: 'Парк победы', coords: [166, 432, 231, 441]},
        {id: 217, name: 'Московская', coords: [170, 442, 231, 451]},
        {id: 218, name: 'Звездная', coords: [181, 452, 231, 461]},
        {id: 219, name: 'Купчино', coords: [186, 462, 231, 471]}
      ]},
    3: {name: '3', stations: [
        {id: 220, name: 'Приморская', coords: [9, 158, 70, 167]},
        {id: 221, name: 'Василеостровская', coords: [13, 206, 47, 206, 47, 196, 57, 196, 57, 206, 90, 206, 90, 215, 13, 215]},
        {id: 222, name: 'Гостиный двор', coords: [155, 206, 222, 206, 222, 196, 232, 196, 232, 206, 222, 206, 222, 214, 155, 214]},
        {id: 223, name: 'Маяковская', coords: [368, 196, 377, 196, 377, 207, 430, 207, 430, 213, 376, 213, 376, 205, 368, 205]},
        {id: 224, name: 'Площадь Александра Невского - I', coords: [488, 267, 488, 255, 500, 255, 500, 240, 647, 240, 647, 255, 502, 255, 502, 267]},
        {id: 225, name: 'Елизаровская', coords: [471, 387, 540, 396]},
        {id: 226, name: 'Ломоносовская', coords: [464, 397, 540, 406]},
        {id: 227, name: 'Пролетарская', coords: [472, 407, 540, 416]},
        {id: 228, name: 'Обухово', coords: [494, 417, 540, 426]},
        {id: 229, name: 'Рыбацкое', coords: [488, 427, 540, 436]}
      ]},
    4: {name: '4', stations: [
        {id: 230, name: 'Спасская', coords: [228, 266, 228, 257, 237, 257, 237, 248, 277, 248, 277, 255, 237, 255, 237, 266]},
        {id: 231, name: 'Достоевская', coords: [252, 267, 309, 267, 309, 257, 319, 257, 319, 266, 309, 266, 309, 275, 252, 275]},
        {id: 232, name: 'Лиговский пр-т', coords: [373, 267, 399, 267, 399, 257, 408, 257, 408, 267, 437, 267, 437, 275, 373, 275]},
        {id: 233, name: 'Площадь Александра Невского - II', coords: []},
        {id: 234, name: 'Новочеркасская', coords: [554, 367, 633, 376]},
        {id: 235, name: 'Ладожская', coords: [576, 377, 633, 386]},
        {id: 236, name: 'Пр-т большевиков', coords: [548, 388, 633, 396]},
        {id: 237, name: 'Улица Дыбенко', coords: [559, 398, 633, 407]}
      ]},
    5: {name: '5', stations: [
        {id: 238, name: 'Комендантский пр-т', coords: [82, 39, 178, 48]},
        {id: 239, name: 'Старая деревня', coords: [82, 49, 158, 58]},
        {id: 240, name: 'Крестовский остров', coords: [82, 89, 177, 98]},
        {id: 241, name: 'Чкаловская', coords: [82, 127, 142, 136]},
        {id: 242, name: 'Спортивная', coords: [82, 137, 141, 146]},
        {id: 243, name: 'Садовая', coords: [176, 249, 216, 249, 216, 257, 225, 257, 225, 266, 216, 266, 216, 257, 176, 257]},
        {id: 244, name: 'Звенигородская', coords: [238, 358, 307, 358, 307, 343, 310, 343, 310, 334, 301, 334, 301, 343, 306, 343, 306, 351, 238, 351]},
        {id: 245, name: 'Волковская', coords: [329, 440, 389, 449]}
      ]}

  }




