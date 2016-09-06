/**
 * Created by JetBrains PhpStorm.
 * User: barba
 * Date: 05.05.11
 * Time: 16:15
 * To change this template use File | Settings | File Templates.
 */

$(function() {  
  chart.init();
  
  //Убер менялка отчётного периода
  $('#fast_switch a').live('click', function(){
    time_interval = $(this).attr('rel');
    interval = new Array(); 
    interval = time_interval.split(';');
    $('input[name=filter\[date_from\]]').val(interval[0]);
    if (interval.length == 2) {
      $('input[name=filter\[date_to\]]').val(interval[1]);
    }
    else {
      $('input[name=filter\[date_to\]]').val(interval[0]);
    }    
    restore_links();
    $(this).after('<span rel="' + time_interval + '">' + $(this).text() + '</span>');
    $(this).hide().remove();
    return false;
  });
  
  //Восстанавливаем ссылки из span
  function restore_links() {
    $('#fast_switch').find('span').each(function(){      
      $(this).after(
        '<a href="#" rel="' + 
        $(this).attr('rel') +'">' + 
        $(this).text() + '</a>'
      );
      $(this).hide().remove();
    });
  }
});


var chart = {
  scriptURL: '/statistic',

  init: function() {
    
    var that = this;
    that._getData();

    $('form.statParams').find('input[type="submit"]').click(function(){
      that._getData();
      return false;
    });
  },

 drawChart: function(data) {
   var chart_data = {
        dates: [],
        active: [],
        added: [],
        inactive: []
       };
   $.each(data, function(){
     chart_data.dates.push(this.date);
     chart_data.active.push(this.active*1);
     chart_data.added.push(this.added*1);
     chart_data.inactive.push(this.inactive*1);
   });

  return new Highcharts.Chart({
        chart: {
          renderTo: 'bars',
          defaultSeriesType: 'column',
          showAxes: false
        },
        title: {
          text: null
        },
        xAxis: {
          categories: chart_data.dates,
          tickmarkPlacement: 'on',
          tickWidth: 1,
          tickLength: 3,
          lineColor: '#a8a8a8',
          gridLineDashStyle: 'ShortDot',
          gridLineWidth: 1,
          gridLineColor: '#a8a8a8',
          labels: {
            style: {
              color: '#000000',
              fontFamily: 'Tahoma, Verdana, sans-serif',
              fontSize: '11px'
            }

          }
        },
        tooltip: {
          formatter: function() {
            return '' +
                this.series.name + ': ' + this.y + '';
          }
        },
        colors: [
          '#a9e97a',
          '#81c42d',
          '#ffc682'
        ],
        legend: {
          align: '',
          borderWidth: 0,
          borderRadius: 0,
          labelFormatter: function() {
            return '— ' + this.name;
          }
        },
        yAxis: {
          plotLines: [
            {
              color: '#a8a8a8',
              width: 1,
              value: 0,
              dashStyle: 'ShortDot'
            }
          ],
          labels: {
            style: {
              color: '#000000',
              fontFamily: 'Tahoma, Verdana, sans-serif',
              fontSize: '11px'
            }
          },
          gridLineDashStyle: 'ShortDot',
          gridLineColor: '#a8a8a8',
          gridLineWidth: 0,
          lineColor: '#a8a8a8',
          lineWidth: 1,
          tickWidth: 1,
          tickLength: 3,
          title: {
            rotation: 0,
            align: 'high',
            style: {
              color: '#a8a8a8',
              fontFamily: 'Tahoma, Verdana, sans-serif',
              fontSize: '9px',
              fontWeight: 'normal'
            },
            text: 'Количество'
          }
        },
        plotOptions: {
          column: {
            shadow: false,
            borderWidth: 0,
            stacking: 'normal'
          }
        },
        credits: {
          enabled: false
        },
        series: [
          {
            name: 'Активные',
            data: chart_data.active
          },
          {
            name: 'Добавленные',
            data: chart_data.added
          },
          {
            name: 'Неактивные',
            data: chart_data.inactive
          }
        ]
      });
  },

  buildTable: function(data) {
    var html = '';
    $.each(data, function(){
      html += '<tr>'
      + '<td>' + this.date + '</td>'
      + '<td>' + this.active + '</td>'
      + '<td>' + this.added + '</td>'
      + '<td>' + this.inactive + '</td>'
      + '</tr>';
    });

    $('div.contentStatistics').find('table.cute-table tbody').empty().append(html);
  },

  _getData: function() {    
    $.post(
      this.scriptURL,
      $('form.statParams').closest('form').serialize(),
      function(data){                
        chart.drawChart(data);
        chart.buildTable(data);
      },
      'json'
    );
  }
};