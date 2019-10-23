@extends('layouts.dashboard')
@section('content')
<style>
.pdfobject-container { height: 30rem; border: 1rem solid rgba(0,0,0,.1); }
</style>
<div id="example1"></div>
        <div class="flex-center position-ref full-height">
            <div class="content"> hello world
            </div>
        </div>
<script>PDFObject.embed("{{env('APP_URL')}}/public/files/termsandcond.pdf", "#example1");</script>
@endsection


