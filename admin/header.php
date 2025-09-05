<?php if(isset($head_title)){$head_title = $head_title;}else{$head_title= "Jsnj Infomedia";} ?>
<style>
    #sidebarToggle {
    background-color: transparent;
    border: 2px solid #ffffff;
    color: rgb(12 110 253);
    padding: 10px 12px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.2);
}

#sidebarToggle:hover {
    background-color: #0ccaf0;
    color: #333;
    box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
    transform: scale(1.05);
}

#sidebarToggle i {
    font-size: 18px;
    transition: transform 0.3s ease;
}

#sidebarToggle:hover i {
    transform: rotate(90deg);
}

</style>
<nav class="navbar p-0">
    <div class="container-fluid py-2">
    <button id="sidebarToggle">
    <i class="fas fa-times" id="sidebarToggleIcon"></i>
</button>
<!-- In header.php, probably in the navbar -->
<ul class="navbar-nav ms-auto">
    <li class="nav-item">
        <a class="nav-link" href="#" id="activity-log-btn" title="View Activity Log">
            <i class="fas fa-bell"></i>
        </a>
    </li>
    <!-- other nav items -->
</ul>

<!-- Add this Modal HTML at the end of your body, in foot.php -->
<div class="modal fade" id="activityLogModal" tabindex="-1" aria-labelledby="activityLogModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="activityLogModalLabel">Recent Employee Activity</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group list-group-flush" id="activity-log-list">
            <!-- Content will be loaded here by JavaScript -->
            <li class="list-group-item text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </li>
        </ul>
      </div>
    </div>
  </div>
</div>
        <span class="navbar-brand text-white mb-0 h1 p-0"><img src="../assets/logo/chhavi_logo.png" style="width:100px;position: absolute;
    top: 5px;
    right: 10px;" alt=""></span>
    </div>
</nav>