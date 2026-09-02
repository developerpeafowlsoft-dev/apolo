<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::all();

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        Page::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'url' => 'page/'.Str::slug($request->title),
            'description' => $request->content,
            'is_active' => true,
            'is_default' => false,
            'is_editable' => true,
        ]);

        return to_route('admin.page.index')->withSuccess('created successfully');
    }

    public function show(Page $page)
    {
        if (!$page) {
            return back()->withError('page not found');
        }

        return view('admin.pages.show', compact('page'));
    }

    public function edit(Page $page)
    {
        if (! $page) {
            return back()->withError('page not found');
        }

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        if (! $page) {
            return back()->withError('page not found');
        }

        $page->update([
            'title' => $request->title,
            'slug' => $page->is_default ? $page->slug : Str::slug($request->title),
            'url' => $page->is_default ? $page->slug : 'page/'.Str::slug($request->title),
            'description' => $request->content,
        ]);

        return to_route('admin.page.index')->withSuccess('updated successfully');
    }

    public function destroy(Page $page)
    {
        if ($page->is_default) {
            return back()->withError('cannot delete default page');
        }

        $page->delete();

        return back()->withSuccess('deleted successfully');
    }
}
