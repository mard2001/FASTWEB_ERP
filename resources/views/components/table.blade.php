<div id="loadingScreen" class="w-100 h-100 d-flex justify-content-center align-items-center loadingScreen">
    <span class="loader" style="height: 42px; width: 42px"></span>
</div>

<div class="w-100 opacity-0" id="dattableDiv" style="font-size: 0.68rem; color: #33363d !important">
    <table class="mdl-data-table w-100 rmvBorder {{$class ?? ""}}" id="{{ $id ?? "getXmlData" }}">
        <thead style="background: linear-gradient(to right, #1E3C72, #33336F ); color: #FFF;">
            <tr>
                {{ $td }}
            </tr>
        </thead>
    </table>
</div>