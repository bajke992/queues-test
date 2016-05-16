@extends('layouts.app')

@section('content')
    <button onclick="send();">Ready</button>
    <input type="text" id="page" placeholder="Page">
    {{--<button onclick="collectFinishedData();">Finished</button>--}}

    {{--<button style="float: right;" onclick="sendFinished();">Send</button>--}}
    <table border="1">

    </table>
@endsection

@section('js')
    <script>
        $table = $('table');

        Array.prototype.remove = function () {
            var what, a = arguments, L = a.length, ax;
            while (L && this.length) {
                what = a[--L];
                while ((ax = this.indexOf(what)) !== -1) {
                    this.splice(ax, 1);
                }
            }
            return this;
        };

        Array.prototype.clone = function () {
            var b = new Array(this.length);
            var i = this.length;
            while (i--) {
                b[i] = this[i];
            }
            return b;
        };

        Array.prototype.uniqueObjects = function () {
            var newArr = [];
            var unique = {};
            this.forEach(function (item) {
                if (!unique[item.matchId]) {
                    newArr.push(item);
                    unique[item.matchId] = item;
                }
            });

            return newArr;
        };

        var $results = null;
        var $ls = null;
        var $page = 1;

        $(document).ready(function () {
            populateData();
            setOnHover();

            $table.floatThead();
        });

        var interval = true;

        function send() {
            $table.floatThead('destroy');

            if(interval) {
                setInterval(send, 2 * 300000);
                interval = false;
            }
            if($('#page').val() !== "") $page = $('#page').val();
            $.ajax({
                url: '{{ URL::route('offer.ready', ['','']) }}pageNumber=' + $page,
                success: function (data) {
                    $results = null;
                    $results = (JSON.parse(data)).matches;
                    if ($results.length === 0) return;
                    $ls = null;
                    if(location.search === "") {
                        if (window.localStorage.hasOwnProperty('ready')) window.localStorage.removeItem('ready');
                    }
                    checkLS();
                    populateData();

                    setOnHover();

                    $table.floatThead();
//                    collectFinishedData();
                }
            });
        }

//      history template
//        matchHistory = [
//            {
//                "matchId" : 2257073,
//                "odds" : [
//                    {
//                        "name": "1",
//                        "history": [
//                            { "timestamp" : "2016-03-12 12:22:33", "value" : "1,25" },
//                            { "timestamp" : "2016-03-12 12:27:33", "value" : "1,35" },
//                            { "timestamp" : "2016-03-12 12:32:33", "value" : "1,30" }
//                        ]
//                    },
//                    {
//                        "name" : "X",
//                        "history": [
//                            { "timestamp" : "2016-03-12 12:22:33", "value" : "3,10" },
//                            { "timestamp" : "2016-03-12 12:27:33", "value" : "3,00" },
//                            { "timestamp" : "2016-03-12 12:32:33", "value" : "2,95" }
//                        ]
//                    },
//                    {
//                        "name" : "2",
//                        "history": [
//                            { "timestamp" : "2016-03-12 12:22:33", "value" : "2,20" },
//                            { "timestamp" : "2016-03-12 12:27:33", "value" : "2,30" },
//                            { "timestamp" : "2016-03-12 12:32:33", "value" : "2,25" }
//                        ]
//                    }
//                ]
//            }
//        ];
//

        function checkHistory(history, matchId, name){
            oddHistory = [];
            history.forEach(function (item) {
                if(item.matchId === matchId) {
                    item.odds.forEach(function (odd) {
                        if (odd.name === name) {
                            oddHistory = odd.history;
                        }
                    });
                }
            });

            return oddHistory;
        }

//      Odds template
//        oddsToInsert = [
//            { "name" : "1", "value" : "1,95"},
//            { "name" : "X", "value" : "4,05"},
//            { "name" : "2", "value" : "3,35"}
//        ];
        function makeOddsHistory(history, matchId, odds, check) {
            var matchIndex = null;

            var $time = new Date();
            var hours = ($time.getHours() < 10) ? "0" + $time.getHours() : $time.getHours();
            var minutes = ($time.getMinutes() < 10) ? "0" + $time.getMinutes() : $time.getMinutes();
            var seconds = ($time.getSeconds() < 10) ? "0" + $time.getSeconds() : $time.getSeconds();
//            var year = $time.getFullYear();
            var day = ($time.getDate() < 10) ? "0" + $time.getDate() : $time.getDate();
            var month = ($time.getMonth() < 10) ? "0" + ($time.getMonth() + 1) : ($time.getMonth() + 1);

            if(check) {
                history.forEach(function (item, index) {
                    if(item.matchId === matchId){
                        matchIndex = index;
                    }
                });

                odds.forEach(function (item) {
                    history[matchIndex].odds.forEach(function (odd) {
                        var lastItem = odd.history.length;
                        var direction = '';

                        if(item.name === odd.name && item.value !== odd.history[lastItem - 1].value ) {
                            if(item.value > odd.history[lastItem - 1].value) direction = '&uarr;';
                            if(item.value < odd.history[lastItem - 1].value) direction = '&darr;';
                            odd.history.push({
                                "timestamp" : month + "-" + day + " " + hours + ":" + minutes + ":" + seconds,
                                "value" : item.value,
                                "direction" : direction
                            });
                        }
                    });
                });

            } else {
                var newItem = {
                    "matchId" : matchId,
                    "odds" : []
                };

                odds.forEach(function (item) {
                    newItem.odds.push({
                        "name" : item.name,
                        "history" : [
                            {
                                "timestamp" : month + "-" + day + " " + hours + ":" + minutes + ":" + seconds,
                                "value" : item.value,
                            }
                        ]
                    });
                });

                history.push(newItem);
            }

            return history;
        }

        function pushHistory(matchId, odds){
            if(!window.localStorage.hasOwnProperty('history')){
                window.localStorage.setItem('history', JSON.stringify([]));
            }

            var history = JSON.parse(window.localStorage.getItem('history'));
            var check = false;

            history.forEach(function (item) {
                if(item.matchId === matchId){
                    check = true;
                }
            });

            history = makeOddsHistory(history, matchId, odds, check);

            window.localStorage.setItem('history', JSON.stringify(history));
            return history;
        }

        function checkLS() {
            if (!window.localStorage.hasOwnProperty('ready')) {
                window.localStorage.setItem('ready', JSON.stringify($results));
            }
            $ls = JSON.parse(window.localStorage.getItem('ready'));

            $tmp = $ls.concat($results);
            $newLS = $tmp.uniqueObjects();

            window.localStorage.removeItem('ready');
            window.localStorage.setItem('ready', JSON.stringify($newLS));
        }

        function setOnHover(){
            var backgroundColor = 'yellow';

            $('[data-id]').each(function (index, item) {
                var history = JSON.parse(localStorage.getItem('history'));
                var matchId = $(item).data('id');
                // start 1
                $('[data-id=' + matchId + '] .1').hover(function () {
                    var historyArray = checkHistory(history, matchId, "1");
                    var timestamps = "";
                    var values = "";

                    historyArray.forEach(function (item) {
                        timestamps += item.timestamp + "<br />";
                        values += item.value + "<br />";
                    });

                    $(this).append(
                            $('<div />').attr({ 'id' : matchId + '-1' }).css({
                                width: 170,
                                height: 'auto',
                                background: backgroundColor,
                                position: 'absolute',
                                bottom: 20,
                                'z-index': 10000,
                                padding: 5
                            }).append(
                                    $('<div />').css({
                                        float: 'left',
                                        'padding-right': 10

                                    }).html(timestamps)
                            ).append(
                                    $('<div />').css({
                                        float: 'right',
                                        'padding-left': 10

                                    }).html(values)
                            )
                    );

                    $('#' + matchId + '-1').append(
                            $('<div />').attr({ 'class' : 'arrows' }).css({ float: 'right' })
                    );

                    historyArray.forEach(function (item) {
                        var dirColor = '';
                        if(item.direction === '&uarr;') dirColor = 'green';
                        if(item.direction === '&darr;') dirColor = 'red';
                        $('#' + matchId + '-1 .arrows').append(
                                $('<div />').css({
                                    color: dirColor,
                                    height: 20,
                                    float: 'right',
                                    clear: 'both'
                                }).html(item.direction)
                        );
                    });
                }, function () {
                    $('#' + matchId + '-1').remove();
                });
                // end 1
                // start 2
                $('[data-id=' + matchId + '] .X').hover(function () {
                    var historyArray = checkHistory(history, matchId, "X");
                    var timestamps = "";
                    var values = "";

                    historyArray.forEach(function (item) {
                        timestamps += item.timestamp + "<br />";
                        values += item.value + "<br />";
                    });

                    $(this).append(
                            $('<div />').attr({ 'id' : matchId + '-X' }).css({
                                width: 170,
                                height: 'auto',
                                background: backgroundColor,
                                position: 'absolute',
                                bottom: 20,
                                'z-index': 10000,
                                padding: 5
                            }).append(
                                    $('<div />').css({
                                        float: 'left',
                                        'padding-right': 10

                                    }).html(timestamps)
                            ).append(
                                    $('<div />').css({
                                        float: 'right',
                                        'padding-left': 10

                                    }).html(values)
                            )
                    );

                    $('#' + matchId + '-X').append(
                            $('<div />').attr({ 'class' : 'arrows' }).css({ float: 'right' })
                    );

                    historyArray.forEach(function (item) {
                        var dirColor = '';
                        if(item.direction === '&uarr;') dirColor = 'green';
                        if(item.direction === '&darr;') dirColor = 'red';
                        $('#' + matchId + '-X .arrows').append(
                                $('<div />').css({
                                    color: dirColor,
                                    height: 20,
                                    float: 'right',
                                    clear: 'both'
                                }).html(item.direction)
                        );
                    });
                }, function () {
                    $('#' + matchId + '-X').remove();
                });
                //end X
                //start 2
                $('[data-id=' + matchId + '] .2').hover(function () {
                    var historyArray = checkHistory(history, matchId, "2");
                    var timestamps = "";
                    var values = "";

                    historyArray.forEach(function (item) {
                        timestamps += item.timestamp + "<br />";
                        values += item.value + "<br />";
                    });

                    $(this).append(
                            $('<div />').attr({ 'id' : matchId + '-2' }).css({
                                width: 170,
                                height: 'auto',
                                background: backgroundColor,
                                position: 'absolute',
                                bottom: 20,
                                'z-index': 10000,
                                padding: 5
                            }).append(
                                    $('<div />').css({
                                        float: 'left',
                                        'padding-right': 10

                                    }).html(timestamps)
                            ).append(
                                    $('<div />').css({
                                        float: 'right',
                                        'padding-left': 10

                                    }).html(values)
                            )
                    );
                    $('#' + matchId + '-2').append(
                            $('<div />').attr({ 'class' : 'arrows' }).css({ float: 'right' })
                    );

                    historyArray.forEach(function (item) {
                        var dirColor = '';
                        if(item.direction === '&uarr;') dirColor = 'green';
                        if(item.direction === '&darr;') dirColor = 'red';
                        $('#' + matchId + '-2 .arrows').append(
                                $('<div />').css({
                                    color: dirColor,
                                    height: 20,
                                    float: 'right',
                                    clear: 'both'
                                }).html(item.direction)
                        );
                    });
                }, function () {
                    $('#' + matchId + '-2').remove();
                });
                // end 2
                // start 1X
                $('[data-id=' + matchId + '] .1X').hover(function () {
                    var historyArray = checkHistory(history, matchId, "1X");
                    var timestamps = "";
                    var values = "";

                    historyArray.forEach(function (item) {
                        timestamps += item.timestamp + "<br />";
                        values += item.value + "<br />";
                    });

                    $(this).append(
                            $('<div />').attr({ 'id' : matchId + '-1X' }).css({
                                width: 170,
                                height: 'auto',
                                background: backgroundColor,
                                position: 'absolute',
                                bottom: 20,
                                'z-index': 10000,
                                padding: 5
                            }).append(
                                    $('<div />').css({
                                        float: 'left',
                                        'padding-right': 10

                                    }).html(timestamps)
                            ).append(
                                    $('<div />').css({
                                        float: 'right',
                                        'padding-left': 10

                                    }).html(values)
                            )
                    );
                    $('#' + matchId + '-1X').append(
                            $('<div />').attr({ 'class' : 'arrows' }).css({ float: 'right' })
                    );

                    historyArray.forEach(function (item) {
                        var dirColor = '';
                        if(item.direction === '&uarr;') dirColor = 'green';
                        if(item.direction === '&darr;') dirColor = 'red';
                        $('#' + matchId + '-1X .arrows').append(
                                $('<div />').css({
                                    color: dirColor,
                                    height: 20,
                                    float: 'right',
                                    clear: 'both'
                                }).html(item.direction)
                        );
                    });
                }, function () {
                    $('#' + matchId + '-1X').remove();
                });
                // end 1X
                // start 12
                $('[data-id=' + matchId + '] .12').hover(function () {
                    var historyArray = checkHistory(history, matchId, "12");
                    var timestamps = "";
                    var values = "";

                    historyArray.forEach(function (item) {
                        timestamps += item.timestamp + "<br />";
                        values += item.value + "<br />";
                    });

                    $(this).append(
                            $('<div />').attr({ 'id' : matchId + '-12' }).css({
                                width: 170,
                                height: 'auto',
                                background: backgroundColor,
                                position: 'absolute',
                                bottom: 20,
                                'z-index': 10000,
                                padding: 5
                            }).append(
                                    $('<div />').css({
                                        float: 'left',
                                        'padding-right': 10

                                    }).html(timestamps)
                            ).append(
                                    $('<div />').css({
                                        float: 'right',
                                        'padding-left': 10

                                    }).html(values)
                            )
                    );
                    $('#' + matchId + '-12').append(
                            $('<div />').attr({ 'class' : 'arrows' }).css({ float: 'right' })
                    );

                    historyArray.forEach(function (item) {
                        var dirColor = '';
                        if(item.direction === '&uarr;') dirColor = 'green';
                        if(item.direction === '&darr;') dirColor = 'red';
                        $('#' + matchId + '-12 .arrows').append(
                                $('<div />').css({
                                    color: dirColor,
                                    height: 20,
                                    float: 'right',
                                    clear: 'both'
                                }).html(item.direction)
                        );
                    });
                }, function () {
                    $('#' + matchId + '-12').remove();
                });
                // end 12
                // start X2
                $('[data-id=' + matchId + '] .X2').hover(function () {
                    var historyArray = checkHistory(history, matchId, "X2");
                    var timestamps = "";
                    var values = "";

                    historyArray.forEach(function (item) {
                        timestamps += item.timestamp + "<br />";
                        values += item.value + "<br />";
                    });

                    $(this).append(
                            $('<div />').attr({ 'id' : matchId + '-X2' }).css({
                                width: 170,
                                height: 'auto',
                                background: backgroundColor,
                                position: 'absolute',
                                bottom: 20,
                                'z-index': 10000,
                                padding: 5
                            }).append(
                                    $('<div />').css({
                                        float: 'left',
                                        'padding-right': 10

                                    }).html(timestamps)
                            ).append(
                                    $('<div />').css({
                                        float: 'right',
                                        'padding-left': 10

                                    }).html(values)
                            )
                    );
                    $('#' + matchId + '-X2').append(
                            $('<div />').attr({ 'class' : 'arrows' }).css({ float: 'right' })
                    );

                    historyArray.forEach(function (item) {
                        var dirColor = '';
                        if(item.direction === '&uarr;') dirColor = 'green';
                        if(item.direction === '&darr;') dirColor = 'red';
                        $('#' + matchId + '-X2 .arrows').append(
                                $('<div />').css({
                                    color: dirColor,
                                    height: 20,
                                    float: 'right',
                                    clear: 'both'
                                }).html(item.direction)
                        );
                    });
                }, function () {
                    $('#' + matchId + '-X2').remove();
                });
                // end X2
            });
        }

        function populateData() {
            if (!window.localStorage.hasOwnProperty('ready')) send();
            $ls = JSON.parse(window.localStorage.getItem('ready'));
            $table = $('table');
            $table.children().remove().delay(2000);

            $table.append(
                    $('<thead/>').css({
                        background: 'white'
                    }).append(
                            $('<tr/>').append(
                                    $('<th/>').text('Vreme')
                            ).append(
                                    $('<th/>').text('Minut')
                            ).append(
                                    $('<th/>').text('Liga')
                            ).append(
                                    $('<th/>').text('Domacin')
                            ).append(
                                    $('<th/>').text('Gost')
                            ).append(
                                    $('<th/>').text('Rezultat')
                            ).append(
                                    $('<th/>').text('Count')
                            ).append(
                                    $('<th/>').text('1')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('X')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('2')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('1X')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('12')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('X2')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('1-1')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('1-X')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('1-2')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('X-1')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('X-X')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('X-2')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('2-1')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('2-X')
                            ).append(
                                    $('<th/>').text('%')
                            ).append(
                                    $('<th/>').text('2-2')
                            ).append(
                                    $('<th/>').text('%')
                            )
                    )
            );

            done = false;

            $ls.forEach(function (item) {
                console.log(item);
                $date = new Date(item.time);
                $hours = ($date.getHours() < 10) ? "0" + $date.getHours() : $date.getHours();
                $minutes = ($date.getMinutes() < 10) ? "0" + $date.getMinutes() : $date.getMinutes();

                if(item.odds.length >= 5 && item.odds[0].subgames[0].bettingGameId === 1 && item.odds[1].subgames[0].bettingGameId === 2 && item.odds[4].subgames[0].bettingGameId === 5){
                    $historyItems = [
                        { "name" : "1", "value" : (item.odds[0].subgames[0].hasOwnProperty('value')) ? item.odds[0].subgames[0].value : ''},
                        { "name" : "X", "value" : (item.odds[0].subgames[1].hasOwnProperty('value')) ? item.odds[0].subgames[1].value : ''},
                        { "name" : "2", "value" : (item.odds[0].subgames[2].hasOwnProperty('value')) ? item.odds[0].subgames[2].value : ''},
                        { "name" : "1X", "value" : (item.odds[1].subgames[0].hasOwnProperty('value')) ? item.odds[1].subgames[0].value : ''},
                        { "name" : "12", "value" : (item.odds[1].subgames[1].hasOwnProperty('value')) ? item.odds[1].subgames[1].value : ''},
                        { "name" : "X2", "value" : (item.odds[1].subgames[2].hasOwnProperty('value')) ? item.odds[1].subgames[2].value : ''},
                        { "name" : "1-1", "value" : (item.odds[4].subgames[0].hasOwnProperty('value')) ? item.odds[4].subgames[0].value : ''},
                        { "name" : "1-X", "value" : (item.odds[4].subgames[1].hasOwnProperty('value')) ? item.odds[4].subgames[1].value : ''},
                        { "name" : "1-2", "value" : (item.odds[4].subgames[2].hasOwnProperty('value')) ? item.odds[4].subgames[2].value : ''},
                        { "name" : "X-1", "value" : (item.odds[4].subgames[3].hasOwnProperty('value')) ? item.odds[4].subgames[3].value : ''},
                        { "name" : "X-X", "value" : (item.odds[4].subgames[4].hasOwnProperty('value')) ? item.odds[4].subgames[4].value : ''},
                        { "name" : "X-2", "value" : (item.odds[4].subgames[5].hasOwnProperty('value')) ? item.odds[4].subgames[5].value : ''},
                        { "name" : "2-1", "value" : (item.odds[4].subgames[6].hasOwnProperty('value')) ? item.odds[4].subgames[6].value : ''},
                        { "name" : "2-X", "value" : (item.odds[4].subgames[7].hasOwnProperty('value')) ? item.odds[4].subgames[7].value : ''},
                        { "name" : "2-2", "value" : (item.odds[4].subgames[8].hasOwnProperty('value')) ? item.odds[4].subgames[8].value : ''}
                    ];
    
                    pushHistory(item.matchId, $historyItems);
                

                $searchResult = {
                    odds: []
                };

                fd = new FormData();

                fd.append('_token', '{{ csrf_token() }}');
                fd.append('item', JSON.stringify(item));

//                if(!done) {
                    $.ajax({
                        url: '{{ URL::route('offer.search') }}',
                        type: 'POST',
                        async: false,
                        processData: false,
                        contentType: false,
                        data: fd,
                        success: function (data) {
                            console.log(data);
                            if (data !== "") $searchResult = data;
                        }
                    });
//                }

                    done = true;
    
                    $table.append(
                            $('<tr/>').attr('data-id', item.matchId).append(
                                    $('<td/>').text($hours + ":" + $minutes)
                            ).append(
                                    $('<td/>').text(item.minute)
                            ).append(
                                    $('<td/>').text(item.competition.shortName)
                            ).append(
                                    $('<td/>').text(item.home)
                            ).append(
                                    $('<td/>').text(item.visitor)
                            ).append(
                                    $('<td/>').text(item.result)
                            ).append(
                                    $('<td/>').text(($searchResult.hasOwnProperty('count')) ? $searchResult.count : "No result")
                            ).append(
                                    $('<td/>').attr({ 'class' : '1' }).css({ position: 'relative'}).text(item.odds[0].subgames[0].value)
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 0) ? calcPercent($searchResult.count, $searchResult.odds[0].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').attr({ 'class' : 'X' }).css({ position: 'relative'}).text(item.odds[0].subgames[1].value)
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 1) ? calcPercent($searchResult.count, $searchResult.odds[1].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').attr({ 'class' : '2' }).css({ position: 'relative'}).text(item.odds[0].subgames[2].value)
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 2) ? calcPercent($searchResult.count, $searchResult.odds[2].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').attr({ 'class' : '1X' }).css({ position: 'relative'}).text(item.odds[1].subgames[0].value)
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 3) ? calcPercent($searchResult.count, $searchResult.odds[3].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').attr({ 'class' : '12' }).css({ position: 'relative'}).text(item.odds[1].subgames[1].value)
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 4) ? calcPercent($searchResult.count, $searchResult.odds[4].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').attr({ 'class' : 'X2' }).css({ position: 'relative'}).text(item.odds[1].subgames[2].value)
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 5) ? calcPercent($searchResult.count, $searchResult.odds[5].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').text((item.odds.hasOwnProperty(4)) ? item.odds[4].subgames[0].value : '')
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 6) ? calcPercent($searchResult.count, $searchResult.odds[6].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').text((item.odds.hasOwnProperty(4)) ? item.odds[4].subgames[1].value : '')
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 7) ? calcPercent($searchResult.count, $searchResult.odds[7].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').text((item.odds.hasOwnProperty(4)) ? item.odds[4].subgames[2].value : '')
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 8) ? calcPercent($searchResult.count, $searchResult.odds[8].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').text((item.odds.hasOwnProperty(4)) ? item.odds[4].subgames[3].value : '')
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 9) ? calcPercent($searchResult.count, $searchResult.odds[9].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').text((item.odds.hasOwnProperty(4)) ? item.odds[4].subgames[4].value : '')
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 10) ? calcPercent($searchResult.count, $searchResult.odds[10].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').text((item.odds.hasOwnProperty(4)) ? item.odds[4].subgames[5].value : '')
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 11) ? calcPercent($searchResult.count, $searchResult.odds[11].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').text((item.odds.hasOwnProperty(4)) ? item.odds[4].subgames[6].value : '')
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 12) ? calcPercent($searchResult.count, $searchResult.odds[12].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').text((item.odds.hasOwnProperty(4)) ? item.odds[4].subgames[7].value : '')
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 13) ? calcPercent($searchResult.count, $searchResult.odds[13].win_count) : "").css({
                                        color: "red"
                                    })
                            ).append(
                                    $('<td/>').text((item.odds.hasOwnProperty(4)) ? item.odds[4].subgames[8].value : '')
                            ).append(
                                    $('<td/>').text(($searchResult.odds.length > 14) ? calcPercent($searchResult.count, $searchResult.odds[14].win_count) : "").css({
                                        color: "red"
                                    })
                            )
                    );
                }
            });
        }

        function collectFinishedData() {
            $.ajax({
                url: '{{ URL::route('home') }}',
                success: function () {
                    console.log('successFinishedData');
                }
            })
        }

        var sendData = {
            matchId: 1,
            odds: [
                {
                    id: 1,
                    subgames: [
                        {
                            id: 1,
                            bettingGameId: 1,
                            value: "1,35",
                            winStatus: "LOSE"
                        },
                        {
                            id: 2,
                            bettingGameId: 1,
                            value: "4,40",
                            winStatus: "LOSE"
                        },
                        {
                            id: 3,
                            bettingGameId: 1,
                            value: "9,00",
                            winStatus: "LOSE"
                        }]
                },
                {
                    id: 2,
                    subgames: [
                        {
                            id: 1,
                            bettingGameId: 2,
                            value: "1,03",
                            winStatus: "LOSE"
                        },
                        {
                            id: 2,
                            bettingGameId: 2,
                            value: "1,17",
                            winStatus: "LOSE"
                        },
                        {
                            id: 3,
                            bettingGameId: 2,
                            value: "2,95",
                            winStatus: "LOSE"
                        }
                    ]
                },
                {
                    id: 3,
                    subgames: []
                },
                {
                    id: 4,
                    subgames: []
                },
                {
                    id: 5,
                    subgames: [
                        {
                            id: 1,
                            bettingGameId: 5,
                            value: "1,95",
                            winStatus: "LOSE"
                        },
                        {
                            id: 2,
                            bettingGameId: 5,
                            value: "23,0",
                            winStatus: "LOSE"
                        },
                        {
                            id: 3,
                            bettingGameId: 5,
                            value: "70,0",
                            winStatus: "LOSE"
                        },
                        {
                            id: 4,
                            bettingGameId: 5,
                            value: "3,90",
                            winStatus: "LOSE"
                        },
                        {
                            id: 5,
                            bettingGameId: 5,
                            value: "6,50",
                            winStatus: "LOSE"
                        },
                        {
                            id: 6,
                            bettingGameId: 5,
                            value: "18,0",
                            winStatus: "LOSE"
                        },
                        {
                            id: 7,
                            bettingGameId: 5,
                            value: "28,0",
                            winStatus: "LOSE"
                        },
                        {
                            id: 8,
                            bettingGameId: 5,
                            value: "23,0",
                            winStatus: "LOSE"
                        },
                        {
                            id: 9,
                            bettingGameId: 5,
                            value: "17,0",
                            winStatus: "LOSE"
                        }
                    ]
                }
            ]
        };

        function collectData() {
            $matchId = $('#matchId').data('id');
            var $a = $.extend(true, {}, sendData);

            $a.matchId = $matchId;

            $('input[type=checkbox]').each(function () {
                $item = $(this);

                switch ($item.attr('id')) {
                    case '1':
                    {
                        $a.odds[0].subgames[0].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[0].subgames[0].winStatus = "WIN";
                        break;
                    }
                    case 'X':
                    {
                        $a.odds[0].subgames[1].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[0].subgames[1].winStatus = "WIN";
                        break;
                    }
                    case '2':
                    {
                        $a.odds[0].subgames[2].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[0].subgames[2].winStatus = "WIN";
                        break;
                    }
                    case '1X':
                    {
                        $a.odds[1].subgames[0].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[1].subgames[0].winStatus = "WIN";
                        break;
                    }
                    case '12':
                    {
                        $a.odds[1].subgames[1].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[1].subgames[1].winStatus = "WIN";
                        break;
                    }
                    case 'X2':
                    {
                        $a.odds[1].subgames[2].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[1].subgames[2].winStatus = "WIN";
                        break;
                    }
                    case '1-1':
                    {
                        $a.odds[4].subgames[0].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[4].subgames[0].winStatus = "WIN";
                        break;
                    }
                    case '1-X':
                    {
                        $a.odds[4].subgames[1].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[4].subgames[1].winStatus = "WIN";
                        break;
                    }
                    case '1-2':
                    {
                        $a.odds[4].subgames[2].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[4].subgames[2].winStatus = "WIN";
                        break;
                    }
                    case 'X-1':
                    {
                        $a.odds[4].subgames[3].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[4].subgames[3].winStatus = "WIN";
                        break;
                    }
                    case 'X-X':
                    {
                        $a.odds[4].subgames[4].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[4].subgames[4].winStatus = "WIN";
                        break;
                    }
                    case 'X-2':
                    {
                        $a.odds[4].subgames[5].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[4].subgames[5].winStatus = "WIN";
                        break;
                    }
                    case '2-1':
                    {
                        $a.odds[4].subgames[6].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[4].subgames[6].winStatus = "WIN";
                        break;
                    }
                    case '2-X':
                    {
                        $a.odds[4].subgames[7].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[4].subgames[7].winStatus = "WIN";
                        break;
                    }
                    case '2-2':
                    {
                        $a.odds[4].subgames[8].value = $item.attr('name');
                        if ($item.is(':checked')) $a.odds[4].subgames[8].winStatus = "WIN";
                        break;
                    }
                }
            });

            return $a;
        }

        function calcPercent($max, $current) {
            return Math.round(($current / $max * 100) * 100) / 100 + "%";
        }

        function sendFinished() {
            var $a = collectData();

            xhr = new XMLHttpRequest();
            fd = new FormData();

            fd.append('data', JSON.stringify($a));
            fd.append('_token', '{{ csrf_token() }}');

            xhr.open('POST', '{{ URL::route('finished') }}');
            xhr.send(fd);

            $ls.shift();
            window.localStorage.setItem('ready', JSON.stringify($ls));
            populateData();
        }


    </script>
@endsection
