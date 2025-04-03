<div class="container-fluid pageHeaderOuterDiv">
    <div class="d-flex justify-content-between align-items-center pageHeaderMainDiv px-3 py-2">
        <h4 class="" style="margin: 0">
            {{ $title }}
        </h4>

        <div class="btn-group">
            <button type="button" class="headerUserDiv dropdown-toggle px-2 py-1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user  userIcon"></i>
                <span id="userName"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button class="dropdown-item" type="button">Profile</button></li>
              <li><button class="dropdown-item" type="button">Logout</button></li>
            </ul>
        </div>
    </div>
</div>