/**
 * Created with JetBrains PhpStorm.
 * User: barba
 * Date: 01.03.12
 * Time: 13:17
 * To change this template use File | Settings | File Templates.
 */

var words = [

  ["ипотека","ипотеки","ипотеке","ипотеку","ипотекой","Ипотека","Ипотеки","Ипотеке","Ипотеку","Ипотекой"],
  ["кредит","кредита","кредиту","кредитом","кредите","Кредит","Кредита","Кредиту","Кредитом","Кредите"],
  ["рассрочка","рассрочки","рассрочке","рассрочку","рассрочкой","Рассрочка","Рассрочки","Рассрочке","Рассрочку","Рассрочкой"],
  ["квартира","квартиры","квартире","квартиру","квартирой","Квартира","Квартиры","Квартире","Квартиру","Квартирой"],
  ["дом","дома","дому","домом","доме","Дом","Дома","Дому","Домом","Доме"]
];


/**
 * Добавляет картинку-врезку - ссылку после первого параграфа в тексте
 *
 * @param sibling
 */
function setIncutPic(sibling) {
  var $incut = $('#header').children('.free-consult').find('a.popup').clone(true);

  $incut.append('<img src="/images/vrezkavtext.jpg">');
  sibling.after($('<div class="float-img"></div>').append($incut));
}

/**
 * Заменяет count слов, взятых из массива words_arr в параграфах
 *
 * @param paragraphs - jquery объект с параграфами в которых идет замена
 * @param words_arr - массив массивов слов для замены
 * @param count - максимальное количество заменяемых слов в тексте
 */
function setIncutWords(paragraphs, words_arr, count) {
  var incut_count = count || 5,
      current_word, paragraph_indexes;

  if (words_arr.length > 0) {

    while (incut_count > 0) {

      for (var i = words_arr.length - 1; i >= 0; i--) {
        var r = getRandomElement(words_arr[i]);
        current_word = r.value;
        paragraph_indexes = findMatches(paragraphs, current_word);

        if (paragraph_indexes.length > 0) {
          replaceWord(current_word, paragraphs, paragraph_indexes);
          incut_count--;
        } else {
          words_arr[i].splice(r.index, 1);
          if (words_arr[i].length === 0) {
            words_arr.splice(i, 1);
          }
        }

        if (words_arr.length === 0) {
          incut_count = 0;
        }

        if (incut_count === 0) {
          break;
        }

      }

    }
  }

}


/**
 *
 * @param p - jquery объект с параграфами, в которых ищем вхождение слова
 * @param word - слово, вхождение которого ищем
 * @return {Array} - массив с идексами параграфов, в которых есть искомое слово
 */
function findMatches(p, word) {
  var p_indexes = [];

  p.each(function(i){
    if (word === 'Ипотека') {
      console.log(this.innerHTML, this.innerHTML.match(makeRegexp(word)))
    }
    if (this.innerHTML.match(makeRegexp(word)) !== null) {
      p_indexes.push(i);
    }
  });
  return p_indexes;
}


/**
 *
 * @param word - слово, которое менем
 * @param p - jquery объект с параграфами, в которых меняем слово
 * @param indexes - массив с индексами параграфов, в которых есть слово, которое меняем
 */
function replaceWord(word, p, indexes) {
  var index = getRandomElement(indexes).value,
      html = p[index].innerHTML,
      replace_regexp = makeRegexp(word);

  p[index].innerHTML = html.replace(replace_regexp, '$1<a href="/form/consult" class="popup inner incut" rel="reg">'+ word +'</a>$2');
}


/**
 *
 * @param array - массив
 * @return {*} возвращает рандомный элемент массива
 */
function getRandomElement(array) {
  var index = Math.floor(Math.random() * array.length);
  return {index: index, value: array[index]};
}

function makeRegexp(word) {
  return new RegExp('(^|\\s)' + word + '([\\s\\.,])');
}