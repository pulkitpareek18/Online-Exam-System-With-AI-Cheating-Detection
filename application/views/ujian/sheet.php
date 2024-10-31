<?php
if(time() >= $soal->waktu_habis)
{
    redirect('ujian/list', 'location', 301);
}
?>
<div class="row">
    <div class="col-sm-3">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Question Navigation</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body text-center" id="tampil_jawaban">
            </div>
        </div>
    </div>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YOLO Detection</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.0/socket.io.min.js"></script>
    <style>
        /* Fullscreen red overlay */
        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 0, 0, 0.8); /* Red with opacity */
            color: white;
            font-size: 2em;
            text-align: center;
            padding-top: 20%;
            z-index: 10;
        }
        #tab-alert {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 0, 0.8); /* Yellow with opacity */
            color: black;
            font-size: 2em;
            text-align: center;
            padding-top: 20%;
            z-index: 11;
        }
    </style>
</head>
<body>
    <video style="display: none;" id="video" autoplay></video>
    
    <!-- Red overlay for phone detection -->
    <div id="overlay">
        No cheating! Cell phone detected. Your test has been paused.
    </div>

    <!-- Yellow overlay for tab switch warning -->
    <div id="tab-alert">
        Do not switch tabs! Your test has been paused.
    </div>

    <!-- Siren sound -->
    <audio id="siren" src="http://127.0.0.1:5000/static/siren.mp3" preload="auto"></audio> <!-- Make sure to have the siren.mp3 file in the same directory -->

    <script>
        const socket = io.connect('http://127.0.0.1:5000');
        const video = document.getElementById('video');
        const overlay = document.getElementById('overlay');
        const tabAlert = document.getElementById('tab-alert');
        const siren = document.getElementById('siren');
        let warningCount = 0;  // Initialize warning counter
        let tabSwitchCount = 0;  // Initialize tab switch counter
        let testPaused = false;  // Track if test is paused

        // Access camera
        navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
            video.srcObject = stream;
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            setInterval(() => {
                if (!testPaused) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const dataURL = canvas.toDataURL('image/jpeg');
                    socket.emit('video_frame', dataURL);
                }
            }, 100); // Adjust interval as needed
        });

        // Receive alerts from the server
        socket.on('alert', data => {
            warningCount += 1;  // Increment the warning counter
            
            if (warningCount < 3) {
                alert(`Warning ${warningCount}: Cell phone detected!`);
            } else {
                overlay.style.display = 'block'; // Show overlay and pause test
                siren.play(); // Play siren sound
                testPaused = true;  // Set test as paused
            }
        });

        // Tab switch detection
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // User left the current tab
                tabSwitchCount += 1;
                setTimeout(() => {
                    tabAlert.style.display = 'block';
                }, 100);  // Add delay to allow rendering after switching tabs
                testPaused = true;  // Pause the test
                if (tabSwitchCount >= 3) {
                    siren.play();  // Play siren after 3 tab switches
                }
            } else {
                // User returned to the tab
                tabAlert.style.display = 'none';
                testPaused = false;  // Resume the test
            }
        });
    </script>
</body>
</html>

    <div class="col-sm-9">
        <?=form_open('', array('id'=>'ujian'), array('id'=> $id_tes));?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><span class="badge bg-blue">Question #<span id="soalke"></span> </span></h3>
                <div class="box-tools pull-right">
                    <span class="badge bg-red">Remaining time <span class="sisawaktu" data-time="<?=$soal->tgl_selesai?>"></span></span>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?=$html?>
            </div>
            <div class="box-footer text-center">
                <a class="action back btn btn-info" rel="0" onclick="return back();"><i class="glyphicon glyphicon-chevron-left"></i> Back</a>
                <a class="ragu_ragu btn btn-primary" rel="1" onclick="return tidak_jawab();">Doubtful</a>
                <a class="action next btn btn-info" rel="2" onclick="return next();"><i class="glyphicon glyphicon-chevron-right"></i> Next</a>
                <a class="selesai action submit btn btn-danger" onclick="return simpan_akhir();"><i class="glyphicon glyphicon-stop"></i> Finished</a>
                <input type="hidden" name="jml_soal" id="jml_soal" value="<?=$no; ?>">
            </div>
        </div>
        <?=form_close();?>
    </div>
</div>

<script type="text/javascript">
    var base_url        = "<?=base_url(); ?>";
    var id_tes          = "<?=$id_tes; ?>";
    var widget          = $(".step");
    var total_widget    = widget.length;
</script>

<script src="<?=base_url()?>assets/dist/js/app/ujian/sheet.js"></script>