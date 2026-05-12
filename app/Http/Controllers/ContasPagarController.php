<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ContasPagarController extends Controller
{
    public function index(): View
    {
        return view('contas-pagar.index');
    }

    public function create(): View
    {
        return view('contas-pagar.create');
    }

    public function store() {}
    public function show($id) {}
    public function edit($id) {}
    public function update($id) {}
    public function destroy($id) {}
}
