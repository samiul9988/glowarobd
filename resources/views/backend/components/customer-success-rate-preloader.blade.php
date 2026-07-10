<div class="card p-3 mb-3" id="preloader-card">
    <div class="row align-items-center px-0 mx-0 mb-2">
        <div class="progress mb-0 col-12 px-0 placeholder-wave" style="height: 20px;">
            <div class="progress-bar" role="progressbar" style="width: 100%; background-color: #e9ecef;"></div>
        </div>
    </div>

    <div class="row text-center mt-2">
        <div class="col-md-4">
            <div class="p-2 rounded placeholder-wave" style="background-color: #fff3cd;">
                <div class="font-weight-bold placeholder col-1"></div>
                <small class="placeholder col-4"></small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-2 rounded placeholder-wave" style="background-color: #d4edda;">
                <div class="font-weight-bold placeholder col-1"></div>
                <small class="placeholder col-4"></small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-2 rounded placeholder-wave" style="background-color: #e2e3e5;">
                <div class="font-weight-bold placeholder col-1"></div>
                <small class="placeholder col-4"></small>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse {
        0% { opacity: 0.6; }
        50% { opacity: 1; }
        100% { opacity: 0.6; }
    }

    #preloader-card {
        animation: pulse 1.5s infinite ease-in-out;
    }
</style>
