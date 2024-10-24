<?php

/**
 * Class ShopChart
 */
class ShopChart
{
    public function makeAllShopRevenueChart($chart_data)
    {
        $labels = $chart_data['labels'];
        $labels_json = json_encode($labels);

        $revenue = $chart_data['revenue'];
        $revenue_json = json_encode($revenue);

        $budget = $chart_data['budget'];
        $budget_json = json_encode($budget);

        $budget_rate = $chart_data['budget_rate'];
        $budget_rate_json = json_encode($budget_rate);

        return <<<HTML
<canvas id="chart"></canvas>
<script>
  var chartData = {
			labels: {$labels_json},
			datasets: [{
				type: 'line',
				label: '達成率',
				borderColor: 'blue',
				borderWidth: 2,
				fill: false,
				lineTension: 0,
				data: {$budget_rate_json},
			yAxisID: "y-axis-2",
			datalabels:{
				    color:'blue',
				  formatter: function (value, context) {
                    return value + '%';
                },  
			}
			}, {
				type: 'bar',
				label: '予算',
				backgroundColor: 'red',
				data: {$budget_json},
				yAxisID: "y-axis-1",
				datalabels:{
				    color: 'red',
				  formatter: function (value, context) {
                    return value.toLocaleString('ja');
                  },
                },
			}, {
				type: 'bar',
				label: '実績',
				backgroundColor: 'green',
				data: {$revenue_json},
				yAxisID: "y-axis-1",
				datalabels:{
				    color: 'green',
                    formatter: function (value, context) {
                        return value.toLocaleString('ja');
                    },
                },
			}]

		};
		window.onload = function() {
			var ctx = document.getElementById('chart').getContext('2d');
			window.myMixedChart = new Chart(ctx, {
				type: 'bar',
				data: chartData,
				options: {
					responsive: true,
					title: {
						display: true,
						text: '予算売上一覧'
					},
					tooltips: {
						mode: 'index',
						intersect: true
					},
					scales: {
                        yAxes: [{
                            id: "y-axis-1",   // Y軸のID
                            type: "linear",   // linear固定 
                            position: "left", // どちら側に表示される軸か？
                        }, {
                            id: "y-axis-2",
                            type: "linear", 
                            position: "right",
                        }],
                    },
                    plugins: {
                        datalabels: {
                            anchor: 'end', // データラベルの位置（'end' は上端）
                            align: 'end', // データラベルの位置（'end' は上側）
                            padding: {
                                bottom: 40
                            },
                            font: {
                size: 14
            }
                        }
                    }
				}
			});
		};

</script>
HTML;
    }
}
