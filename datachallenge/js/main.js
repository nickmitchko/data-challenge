/*
 * Copyright (c) 2017 Nicholai G. Mitchko
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

function createLineChart(context, label1, data1, label2, data2) {
    return new Chart(context, {
        type: 'line',
        data: {
            datasets: [{
                fill: true,
                lineTension: 0.2,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderCapStyle: 'butt',
                borderDash: [],
                borderDashOffset: 0.0,
                borderJoinStyle: 'miter',
                borderWidth: 1,
                pointBorderColor: 'rgba(54, 162, 235, 1)',
                pointBackgroundColor: "#fff",
                pointBorderWidth: 1,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointHoverBorderColor: 'rgba(54, 162, 235, 1)',
                pointHoverBorderWidth: 2,
                pointRadius: 3,
                pointHitRadius: 10,
                label: label1,
                data: data1
            }, {
                fill: true,
                lineTension: 0.2,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255,99,132,1)',
                borderCapStyle: 'butt',
                borderDash: [],
                borderDashOffset: 0.0,
                borderJoinStyle: 'miter',
                borderWidth: 1,
                pointBorderColor: 'rgba(255,99,132,1)',
                pointBackgroundColor: "#fff",
                pointBorderWidth: 1,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: 'rgba(255, 99, 132, 0.2)',
                pointHoverBorderColor: 'rgba(255,99,132,1)',
                pointHoverBorderWidth: 2,
                pointRadius: 3,
                pointHitRadius: 10,
                label: label2,
                data: data2
            }
            ]
        },
        options: {
            responsive: true,
            scales: {
                xAxes: [{
                    type: 'linear',
                    position: 'bottom'
                }]
            }
        }
    });
}

function createLineChartSingle(context, label1, data1, xLabel, yLabel) {
    var scatterChart = new Chart(context, {
        type: 'line',
        data: {
            datasets: [{
                fill: true,
                lineTension: 0.2,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderCapStyle: 'butt',
                borderDash: [],
                borderDashOffset: 0.0,
                borderJoinStyle: 'miter',
                borderWidth: 1,
                pointBorderColor: 'rgba(54, 162, 235, 1)',
                pointBackgroundColor: "#fff",
                pointBorderWidth: 1,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointHoverBorderColor: 'rgba(54, 162, 235, 1)',
                pointHoverBorderWidth: 2,
                pointRadius: 2,
                pointHitRadius: 10,
                label: label1,
                data: data1
            }]
        },
        options: {
            responsive: true,
            scales: {
                xAxes: [{
                    type: 'linear',
                    position: 'bottom',
                    scaleLabel: {
                        display: true,
                        labelString: xLabel
                    }
                }],
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: yLabel
                    }
                }]
            }
        }
    });
}

function createDoughnutChart(context, data, labels, percentage) {
    var doughnutChart = new Chart(context, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: data
        },
        animation: {
            animateRotate: true
        },
        options: {
            cutoutPercentage: 80,
            drawPercentage: percentage,
            responsive: true
        }
    });
}

function changeView(idOfView) {
    $('#main-panel').children().each(function () {
        $(this).hide();
    }).promise().done(function () {
        $('#' + idOfView).show();
        switch (idOfView) {
            case 'results':
                makeCharts();
                break;
            case 'submit':
                break;
            case 'admin':
                break;
            default:
                break;
        }
    });
}

function registerChartAddon() {
    Chart.pluginService.register({
        beforeDraw: function (chart) {
            if (chart.config.options.drawPercentage) {

                var width = chart.chart.width,
                    height = chart.chart.height,
                    ctx = chart.chart.ctx;

                ctx.restore();
                var fontSize = (height / 114).toFixed(2);
                ctx.font = fontSize + "em sans-serif";
                ctx.textBaseline = "middle";

                var text = chart.config.options.drawPercentage,
                    textX = Math.round((width - ctx.measureText(text).width) / 2),
                    textY = (height + parseInt(ctx.font) / 2) / 2;
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }
    });
}

function registerPageChanger() {
    $('.mdl-navigation__link').click(function () {
        changeView($(this)[0].dataset.view);
    });
}

function registerAdminForm() {
    $('form#dataChallengeAdminUpload').on("submit", function (e) {
        e.preventDefault();
        e.stopPropagation();
        handleAdminForm();
    });
}

function registerAdminDeleteForm() {
    $('form#dataChallengeAdminDelete').on("submit", function (e) {
        e.preventDefault();
        e.stopPropagation();
        handleAdminDeleteForm();
    });
}

function registerUserForm() {
    $('form#dataChallengeUpload').on("submit", function (e) {
        e.preventDefault();
        e.stopPropagation();
        handleUserForm();
    });
}

function registerDownloadForm() {
    $('form#dataChallengeDownload').on("submit", function (e) {
        e.preventDefault();
        e.stopPropagation();
        handleDownloadForm();
    });
}

function handleDownloadForm() {
    var formData = new FormData($('#dataChallengeDownload')[0]);
    $.ajax({
        url: 'downloadResults.php',  //Server script to process data
        type: 'POST',
        xhr: function () {  // Custom XMLHttpRequest
            var myXhr = $.ajaxSettings.xhr();
            return myXhr;
        },
        /*        Ajax events
         beforeSend: beforeSendHandler,

         error: errorHandler,*/
        success: function (data) {
            download("Results.csv", data);
        },
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });
}

function handleUserForm() {
    var formData = new FormData($('#dataChallengeUpload')[0]);
    formData.append('file', $('#userUploadBtn')[0].files[0]);
    $('#userProgressBar').show();
    $.ajax({
        url: 'uploadUser.php',  //Server script to process data
        type: 'POST',
        xhr: function () {  // Custom XMLHttpRequest
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) { // Check if upload property exists
                myXhr.upload.addEventListener('progress', userProgressHandlingFunction, false); // For handling the progress of the upload
            }
            return myXhr;
        },
        /*        Ajax events
         beforeSend: beforeSendHandler,

         error: errorHandler,*/
        success: function (data) {
            try {
                $('#AUC').show();
                $('#userProgressBar').addClass('mdl-progress-green');
                var results = JSON.parse(data);
                handleSubmissionResult(results);
            } catch (e) {
                $('#userProgressBar').addClass('mdl-progress-red');
            }
            setTimeout(function () {
                $('#userProgressBar').fadeOut("slow", null);
            }, 500);
        },
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });
}

function handleSubmissionResult(json) {
    if (window.chartiii != undefined) {
        window.chartiii.destroy();
    }
    window.chartiii = createDoughnutChart(
        document.getElementById('lastChart1'),
        [{
            data: [1 - json['auc'], json['auc']],
            backgroundColor: [
                "rgb(242,241,239)",
                "#36A2EB"
            ]
        }],
        ["Error", "AUC"],
        (json['auc'] * 100).toFixed(2)
    );
    if (json['auc'] > auc) {
        auc = json['auc'];
        if (window.chartiv != undefined) {
            window.chartiv.destroy();
        }
        window.chartiv = createDoughnutChart(
            document.getElementById('bestChart1'),
            [{
                data: [1 - json['auc'], json['auc']],
                backgroundColor: [
                    "rgb(242,241,239)",
                    "#36A2EB"
                ]
            }],
            ["Error", "AUC"],
            (json['auc'] * 100).toFixed(2)
        );
    }
    if (json['graph'] != undefined) {
        var data = [];
        for (var i = 0; i < json['graph'][0].length; ++i) {
            data[i] = {x: json['graph'][0][i], y: json['graph'][1][i]};
        }
        if (window.chartv != undefined) {
            window.chartv.destroy();
        }
        window.chartv = createLineChartSingle(
            document.getElementById('aucChart1'),
            "AUC",
            data,
            "FPR",
            "TPR"
        );
    }
    $('#userProgressBar').removeClass('mdl-progress-green');
}

function handleAdminForm() {
    var formData = new FormData($('#dataChallengeAdminUpload')[0]);
    formData.append('file', $('#uploadBtn')[0].files[0]);
    $('#adminCreateProgressBar').show();
    $.ajax({
        url: 'uploadAdmin.php',  //Server script to process data
        type: 'POST',
        xhr: function () {  // Custom XMLHttpRequest
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) { // Check if upload property exists
                myXhr.upload.addEventListener('progress', adminProgressHandlingFunction, false); // For handling the progress of the upload
            }
            return myXhr;
        },
        /* Ajax events
         beforeSend: beforeSendHandler,
         success: completeHandler,
         error: errorHandler,*/
        success: function (data) {
            var json = JSON.parse(data);
            if (json['success'] == 1) {
                $('#adminCreateProgressBar').addClass('mdl-progress-green');
            } else {
                $('#adminCreateProgressBar').addClass('mdl-progress-red');
                $('#createText').text(json['message']);
            }
            setTimeout(function () {
                $('#adminCreateProgressBar').fadeOut("slow", function () {
                });
                window.location = 'home.php?page=admin';
            }, 1000);
        },
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });
}

function handleAdminDeleteForm() {
    var formData = new FormData($('#dataChallengeAdminDelete')[0]);
    $('#adminDeleteProgressBar').show();
    $.ajax({
        url: 'deleteAdmin.php',  //Server script to process data
        type: 'POST',
        xhr: function () {  // Custom XMLHttpRequest
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) { // Check if upload property exists
                myXhr.upload.addEventListener('progress', adminDeleteProgressHandlingFunction, false); // For handling the progress of the upload
            }
            return myXhr;
        },
        /* Ajax events
         beforeSend: beforeSendHandler,
         success: completeHandler,
         error: errorHandler,*/
        success: function (data) {
            var json = JSON.parse(data);
            if (json['success'] == 1) {
                $('#adminDeleteProgressBar').addClass('mdl-progress-green');
                $('#option' + json['id']).prop("disabled", true);
            } else {
                $('#adminDeleteProgressBar').addClass('mdl-progress-red');
                $('#delete-text').text(json['message']);
            }
            setTimeout(function () {
                $('#adminDeleteProgressBar').fadeOut("slow", function () {
                });
                window.location = 'home.php?page=admin';
            }, 1000);
        },
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });
}

function userProgressHandlingFunction(e) {
    if (e.lengthComputable) {
        document.querySelector('#userProgressBar').MaterialProgress.setProgress(e.loaded / e.total * 100);
    }
}

function adminDeleteProgressHandlingFunction(e) {
    if (e.lengthComputable) {
        document.querySelector('#adminDeleteProgressBar').MaterialProgress.setProgress(e.loaded / e.total * 100);
    }
}

function adminProgressHandlingFunction(e) {
    if (e.lengthComputable) {
        document.querySelector('#adminCreateProgressBar').MaterialProgress.setProgress(e.loaded / e.total * 100);
    }
}

function enableFileName() {
    var user = document.getElementById("userUploadBtn");
    var admin = document.getElementById("uploadBtn");
    if (user != null) {
        user.onchange = function () {
            document.getElementById("userUploadFile").value = this.files[0].name;
        };
    }
    if (admin != null) {
        admin.onchange = function () {
            document.getElementById("uploadFile").value = this.files[0].name;
        };
    }
}

function handlePersistentPaging() {
    if (window.pageI != undefined) {
        changeView(window.pageI);
    }
}

function initComponents() {
    registerChartAddon();
    registerPageChanger();
    registerUserForm();
    enableFileName();
    handleSubmissionResult({auc: 0.0});
    registerAdminForm();
    registerAdminDeleteForm();
    registerDownloadForm();
    handlePersistentPaging();
    makeCharts();
}

$(document).ready(
    function () {
        setTimeout(function () {
            initComponents();
        }, 200);
    }
);

function download(filename, text) {
    var pom = document.createElement('a');
    pom.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    pom.setAttribute('download', filename);

    if (document.createEvent) {
        var event = document.createEvent('MouseEvents');
        event.initEvent('click', true, true);
        pom.dispatchEvent(event);
    }
    else {
        pom.click();
    }
}

auc = -0.1;
