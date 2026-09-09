@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold text-dark">Manage Categories</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-2" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            Back to Ticket Types
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="row">
    <div class="col-12 col-lg-8 col-xl-7">
        <div class="card fd-card p-4 shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Categories for '{{ $ticketType->name }}'</h5>
                    <p class="text-muted small mb-0">Construct a collapsible 3-level hierarchical category tree (Category 1 -> Category 2 -> Category 3).</p>
                </div>
                <button type="button" class="btn btn-primary d-flex align-items-center" onclick="addRootCategory()">
                    + Add Category 1
                </button>
            </div>

            <!-- Category Tree Container -->
            <div id="tree-container" class="bg-light p-3 rounded border mb-4" style="min-height: 200px;">
                <!-- Dynamically populated via JS -->
            </div>

            <form method="POST" action="{{ route('admin.ticket-types.categories.store', $ticketType) }}" onsubmit="serializeTree()">
                @csrf
                <input type="hidden" name="categories_json" id="categories-json-input">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success px-4">Save Categories</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let categoryTree = @json($tree);
    let uidCounter = 1;

    // Helper to assign unique ID to each node client-side
    function assignUids(nodes) {
        nodes.forEach(node => {
            node.uid = uidCounter++;
            if (node.children) {
                assignUids(node.children);
            }
        });
    }

    // Initialize Tree
    assignUids(categoryTree);
    updateTreeUI();

    function updateTreeUI() {
        const container = document.getElementById('tree-container');
        if (categoryTree.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-diagram-3 text-secondary mb-3" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1.5h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 1 0 2.122l-2.12 2.122a1.5 1.5 0 0 1-1.06.44H1.5A1.5 1.5 0 0 1 0 13.5v-1A1.5 1.5 0 0 1 1.5 11h3.879V7.5H2.121a1.5 1.5 0 0 1-1.06-.44L.16 5.161a1.5 1.5 0 0 1 0-2.122l2.12-2.122a1.5 1.5 0 0 1 1.06-.44H6.5v1.5z"/>
                    </svg>
                    <p class="mb-0">No categories defined yet for this ticket type.</p>
                    <small class="text-muted">Click "+ Add Category 1" above to start building your hierarchy.</small>
                </div>
            `;
            return;
        }
        container.innerHTML = renderTree(categoryTree, 1);
    }

    function renderTree(nodes, level) {
        let html = '<div class="d-flex flex-column gap-2">';
        nodes.forEach(node => {
            const hasChildren = node.children && node.children.length > 0;
            const showAddSub = level < 3;

            html += `
                <div class="tree-node" data-uid="${node.uid}">
                    <div class="d-flex align-items-center gap-2 p-2.5 rounded border bg-white shadow-sm">
                        ${hasChildren ? `
                            <span class="toggle-btn px-1" onclick="toggleCollapse(${node.uid})" style="cursor: pointer; user-select: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down text-secondary" viewBox="0 0 16 16" id="icon-${node.uid}" style="transition: transform 0.2s;">
                                    <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>
                                </svg>
                            </span>
                        ` : `
                            <span class="px-2"></span>
                        `}
                        <span class="fw-semibold text-dark fs-6 me-2">${escapeHtml(node.name)}</span>
                        <span class="badge bg-light text-secondary border small">Category ${level}</span>
                        
                        <div class="ms-auto d-inline-flex gap-1">
                            ${showAddSub ? `
                                <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center py-0 px-2" style="min-height: 28px;" onclick="addSubcategory(${node.uid})">
                                    + Add Sub
                                </button>
                            ` : ''}
                            <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center py-0 px-2" style="min-height: 28px;" onclick="renameCategory(${node.uid})">
                                Rename
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center py-0 px-2" style="min-height: 28px;" onclick="deleteCategory(${node.uid})">
                                Delete
                            </button>
                        </div>
                    </div>
                    
                    ${hasChildren ? `
                        <div class="children-container ms-4 ps-3 border-start mt-2" id="children-${node.uid}">
                            ${renderTree(node.children, level + 1)}
                        </div>
                    ` : ''}
                </div>
            `;
        });
        html += '</div>';
        return html;
    }

    function toggleCollapse(uid) {
        const container = document.getElementById(`children-${uid}`);
        const icon = document.getElementById(`icon-${uid}`);
        if (container) {
            if (container.classList.contains('d-none')) {
                container.classList.remove('d-none');
                icon.style.transform = 'rotate(0deg)';
            } else {
                container.classList.add('d-none');
                icon.style.transform = 'rotate(-90deg)';
            }
        }
    }

    function addRootCategory() {
        const name = prompt("Enter Category 1 Name:");
        if (name && name.trim()) {
            categoryTree.push({
                uid: uidCounter++,
                name: name.trim(),
                children: []
            });
            updateTreeUI();
        }
    }

    function addSubcategory(parentUid) {
        const parentNode = findNode(categoryTree, parentUid);
        if (parentNode) {
            const name = prompt(`Add subcategory under '${parentNode.name}':`);
            if (name && name.trim()) {
                if (!parentNode.children) {
                    parentNode.children = [];
                }
                parentNode.children.push({
                    uid: uidCounter++,
                    name: name.trim(),
                    children: []
                });
                updateTreeUI();
            }
        }
    }

    function renameCategory(uid) {
        const node = findNode(categoryTree, uid);
        if (node) {
            const name = prompt("Enter new name:", node.name);
            if (name && name.trim() && name.trim() !== node.name) {
                node.name = name.trim();
                updateTreeUI();
            }
        }
    }

    function deleteCategory(uid) {
        if (confirm("Are you sure you want to delete this category? (Deleting a category will also delete its subcategories.)")) {
            removeNodeByUid(categoryTree, uid);
            updateTreeUI();
        }
    }

    // Helper functions to manage JS tree object
    function findNode(nodes, uid) {
        for (let node of nodes) {
            if (node.uid === uid) return node;
            if (node.children) {
                let found = findNode(node.children, uid);
                if (found) return found;
            }
        }
        return null;
    }

    function removeNodeByUid(nodes, uid) {
        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].uid === uid) {
                nodes.splice(i, 1);
                return true;
            }
            if (nodes[i].children) {
                let removed = removeNodeByUid(nodes[i].children, uid);
                if (removed) return true;
            }
        }
        return false;
    }

    function serializeTree() {
        document.getElementById('categories-json-input').value = JSON.stringify(categoryTree);
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endsection
