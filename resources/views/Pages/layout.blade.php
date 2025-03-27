@extends('Layout.layout')

@section('html_title')
<title>Page Title</title>
@endsection

@section('title_header')
<x-header title="Title" />
<!-- Title here -->
@endsection

@section('table')
<!-- table -->
@endsection

@section('modal')
<!-- Modal Here -->
@endsection

@section('pagejs')
<!-- javascript here -->
<script>
    const hamBurger = document.querySelector(".btn-toggle");

    hamBurger.addEventListener("click", async function() {
        document.querySelector("#sidebar").classList.toggle("expand");

    });
</script>
@endsection