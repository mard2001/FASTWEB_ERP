@props(['addFunc' => false, 'downloadFunc' => false, 'uploadFunc' => false])

<div class="main-content buttons w-100 overflow-auto d-flex align-items-center px-2 py-2" style="font-size: 12px;">

    @if ($addFunc)
        <div class="btn d-flex justify-content-around px-2 align-items-center me-1" id="addBtn">
            <div class="btnImg me-2" id="addImg">
            </div>
            <span>Add new</span>
        </div>
    @endif

    @if ($downloadFunc)
        <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvDLBtn">
            <div class="btnImg me-2" id="dlImg">
            </div>
            <span>Download Report</span>
        </div>
    @endif

    @if ($uploadFunc)
        <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvUploadShowBtn">
            <div class="btnImg me-2" id="ulImg">
            </div>
            <span>Upload Template</span>
        </div>
    @endif

    {{ $additionalButtons ?? "" }}
</div>
