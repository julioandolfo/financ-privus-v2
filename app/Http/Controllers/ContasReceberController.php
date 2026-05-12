<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ContasReceberController extends Controller
{
    public function index(): View
    {
        return view('contas-receber.index');
    }

    public function create(): View
    {
        return view('contas-receber.create');
    }

    public function store() {}
    public function show($id) {}
    public function edit($id) {}
    public function update($id) {}
    public function destroy($id) {}
}
