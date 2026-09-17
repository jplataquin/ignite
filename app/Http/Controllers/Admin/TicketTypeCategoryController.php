<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryClosure;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketTypeCategoryController extends Controller
{
    /**
     * Display the category tree management view.
     */
    public function index(TicketType $ticketType)
    {
        $categories = Category::where('ticket_type_id', $ticketType->id)
            ->with('ancestorClosures')
            ->get();

        $nodes = [];
        foreach ($categories as $category) {
            $parentClosure = $category->ancestorClosures->where('depth', 1)->first();
            $parentId = $parentClosure ? $parentClosure->ancestor_id : null;

            $nodes[$category->id] = [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $parentId,
                'children' => [],
            ];
        }

        $tree = [];
        foreach ($nodes as $id => &$node) {
            if ($node['parent_id'] === null) {
                $tree[] = &$node;
            } else {
                if (isset($nodes[$node['parent_id']])) {
                    $nodes[$node['parent_id']]['children'][] = &$node;
                }
            }
        }

        return view('admin.ticket-types.categories', compact('ticketType', 'tree'));
    }

    /**
     * Store the updated category tree.
     */
    public function store(Request $request, TicketType $ticketType)
    {
        $request->validate([
            'categories_json' => 'required|string',
        ]);

        $tree = json_decode($request->input('categories_json'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()
                ->with('error', 'Invalid category tree format.')
                ->withInput();
        }

        // Validate tree structure and depth (max 3 levels)
        if (!is_array($tree)) {
            return redirect()->back()
                ->with('error', 'Category tree must be an array.')
                ->withInput();
        }

        if (count($tree) > 0 && !$this->validateTree($tree)) {
            return redirect()->back()
                ->with('error', 'Category tree validation failed. Ensure all categories have names and do not exceed 3 levels of depth.')
                ->withInput();
        }

        $existingCategories = Category::where('ticket_type_id', $ticketType->id)->get();
        $existingIds = $existingCategories->pluck('id')->toArray();
        $newTreeIds = $this->collectIdsFromTree($tree);
        $idsToDelete = array_diff($existingIds, $newTreeIds);

        if (!empty($idsToDelete)) {
            $referencedCategory = DB::table('tickets')
                ->where(function ($query) use ($idsToDelete) {
                    $query->whereIn('category_1_id', $idsToDelete)
                        ->orWhereIn('category_2_id', $idsToDelete)
                        ->orWhereIn('category_3_id', $idsToDelete);
                })
                ->first();
            
            if ($referencedCategory) {
                $catId = null;
                if (in_array($referencedCategory->category_1_id, $idsToDelete)) {
                    $catId = $referencedCategory->category_1_id;
                } elseif (in_array($referencedCategory->category_2_id, $idsToDelete)) {
                    $catId = $referencedCategory->category_2_id;
                } elseif (in_array($referencedCategory->category_3_id, $idsToDelete)) {
                    $catId = $referencedCategory->category_3_id;
                }
                $catName = Category::find($catId)?->name ?? 'Unknown';
                return redirect()->back()
                    ->with('error', "Cannot delete category '{$catName}' because it is currently assigned to one or more tickets.")
                    ->withInput();
            }
        }

        DB::transaction(function () use ($ticketType, $tree, $idsToDelete, $newTreeIds) {
            // Delete categories that are no longer in the tree
            if (!empty($idsToDelete)) {
                Category::whereIn('id', $idsToDelete)->delete();
            }

            // Clear closures for remaining categories
            if (!empty($newTreeIds)) {
                CategoryClosure::whereIn('ancestor_id', $newTreeIds)->delete();
            }

            // Save/update category tree recursively
            foreach ($tree as $node) {
                $this->saveNode($node, $ticketType->id);
            }
        });

        return redirect()->route('admin.ticket-types.index')
            ->with('success', "Categories for '{$ticketType->name}' updated successfully.");
    }

    /**
     * Recursively validate node names and max depth of 3.
     */
    private function validateTree(array $nodes, int $currentDepth = 1): bool
    {
        if ($currentDepth > 3) {
            return false;
        }

        foreach ($nodes as $node) {
            if (!isset($node['name']) || empty(trim($node['name']))) {
                return false;
            }
            if (isset($node['children']) && is_array($node['children'])) {
                if (!$this->validateTree($node['children'], $currentDepth + 1)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recursively collect all existing category IDs from the tree.
     */
    private function collectIdsFromTree(array $nodes): array
    {
        $ids = [];
        foreach ($nodes as $node) {
            if (isset($node['id'])) {
                $ids[] = (int) $node['id'];
            }
            if (isset($node['children']) && is_array($node['children'])) {
                $ids = array_merge($ids, $this->collectIdsFromTree($node['children']));
            }
        }
        return $ids;
    }

    /**
     * Recursively save category nodes and their closure records.
     */
    private function saveNode(array $node, int $ticketTypeId, array $ancestorIds = []): void
    {
        if (isset($node['id'])) {
            // Update existing Category
            $category = Category::findOrFail($node['id']);
            $category->update([
                'name' => trim($node['name']),
                'ticket_type_id' => $ticketTypeId,
            ]);
        } else {
            // Create new Category
            $category = Category::create([
                'name' => trim($node['name']),
                'ticket_type_id' => $ticketTypeId,
            ]);
        }

        $myId = $category->id;

        // 2. Create self-closure record
        CategoryClosure::create([
            'ancestor_id' => $myId,
            'descendant_id' => $myId,
            'depth' => 0,
        ]);

        // 3. Create ancestor-closure records (with appropriate depths)
        $numAncestors = count($ancestorIds);
        foreach ($ancestorIds as $index => $ancestorId) {
            $depth = $numAncestors - $index;
            CategoryClosure::create([
                'ancestor_id' => $ancestorId,
                'descendant_id' => $myId,
                'depth' => $depth,
            ]);
        }

        // 4. Save child nodes recursively
        if (isset($node['children']) && is_array($node['children'])) {
            $newAncestors = array_merge($ancestorIds, [$myId]);
            foreach ($node['children'] as $childNode) {
                $this->saveNode($childNode, $ticketTypeId, $newAncestors);
            }
        }
    }
}
