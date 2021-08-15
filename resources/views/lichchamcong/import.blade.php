<form action="{{ route('import') }}" class="mr-5" enctype="multipart/form-data" method="POST">
    <div class="row">
        {{ csrf_field() }}
        <div class="col-xs-8">
            <input type="file" class="form-control" name="file_tb" required="true">
        </div>
        <button class="btn btn-sm btn-primary" type="submit">import</button>
    </div>
</form>