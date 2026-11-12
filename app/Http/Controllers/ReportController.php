<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Division;
use App\Models\Location;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display a summary of ticket counts grouped per Division and per Department.
     */
    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);
        $tickets = $this->getFilteredTickets($filters);

        $divisions = Division::with('departments')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $ticketTypes = TicketType::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $priorities = Priority::orderBy('level')->get();
        $statuses = ['Valid', 'Done', 'Lapsed'];

        $reportData = $this->buildReportData(
            $tickets,
            $divisions,
            $ticketTypes,
            $locations,
            $priorities,
            $statuses,
            $filters
        );

        return view('reports.index', [
            'reportData' => $reportData,
            'divisions' => $divisions,
            'departments' => $departments,
            'ticketTypes' => $ticketTypes,
            'locations' => $locations,
            'priorities' => $priorities,
            'statuses' => $statuses,
            'filters' => $filters,
        ]);
    }

    /**
     * Export the summary data to CSV format.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->extractFilters($request);
        $tickets = $this->getFilteredTickets($filters);

        $divisions = Division::with('departments')->orderBy('name')->get();
        $ticketTypes = TicketType::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $priorities = Priority::orderBy('level')->get();
        $statuses = ['Valid', 'Done', 'Lapsed'];

        $reportData = $this->buildReportData(
            $tickets,
            $divisions,
            $ticketTypes,
            $locations,
            $priorities,
            $statuses,
            $filters
        );

        $response = new StreamedResponse(function () use ($reportData) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Division',
                'Department',
                'Ticket Type',
                'Location',
                'Priority',
                'Status',
                'Count',
            ]);

            foreach ($reportData['divisions'] as $division) {
                foreach ($division['departments'] as $dept) {
                    foreach ($dept['rows'] as $row) {
                        fputcsv($handle, [
                            $division['name'],
                            $dept['name'],
                            $row['ticket_type'],
                            $row['location'],
                            $row['priority'],
                            $row['status'],
                            $row['count'],
                        ]);
                    }
                }
            }

            fclose($handle);
        });

        $filename = 'ticket-report-'.now()->format('Y-m-d-His').'.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }

    /**
     * Extract and normalize filters from request.
     *
     * @return array<string, mixed>
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'division_id' => $request->filled('division_id') ? (int) $request->input('division_id') : null,
            'department_id' => $request->filled('department_id') ? (int) $request->input('department_id') : null,
            'ticket_type_id' => $request->filled('ticket_type_id') ? (int) $request->input('ticket_type_id') : null,
            'priority_id' => $request->filled('priority_id')
                ? (int) $request->input('priority_id')
                : ($request->filled('priority_option_id') ? (int) $request->input('priority_option_id') : null),
            'location_id' => $request->filled('location_id') ? (int) $request->input('location_id') : null,
            'status' => $request->filled('status') ? (string) $request->input('status') : null,
            'date_from' => $request->filled('date_from') ? (string) $request->input('date_from') : null,
            'date_to' => $request->filled('date_to') ? (string) $request->input('date_to') : null,
        ];
    }

    /**
     * Get tickets based on applied filters.
     *
     * @param  array<string, mixed>  $filters
     */
    protected function getFilteredTickets(array $filters): Collection
    {
        $query = Ticket::query()->select([
            'id',
            'ticket_number',
            'division_id',
            'department_id',
            'ticket_type_id',
            'location_id',
            'priority_option_id',
            'status',
            'created_at',
        ]);

        if ($filters['division_id']) {
            $query->where('division_id', $filters['division_id']);
        }

        if ($filters['department_id']) {
            $query->where('department_id', $filters['department_id']);
        }

        if ($filters['ticket_type_id']) {
            $query->where('ticket_type_id', $filters['ticket_type_id']);
        }

        if ($filters['priority_id']) {
            $query->where('priority_option_id', $filters['priority_id']);
        }

        if ($filters['location_id']) {
            $query->where('location_id', $filters['location_id']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    /**
     * Build report data structure grouped per Division and per Department.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function buildReportData(
        Collection $tickets,
        Collection $divisions,
        Collection $ticketTypes,
        Collection $locations,
        Collection $priorities,
        array $statuses,
        array $filters
    ): array {
        $overallBreakdown = $this->computeBreakdown(
            $tickets,
            $ticketTypes,
            $locations,
            $priorities,
            $statuses
        );

        $divisionsData = [];

        // Group tickets by division_id
        $ticketsByDivision = $tickets->groupBy(fn ($ticket) => $ticket->division_id ?? 0);

        foreach ($divisions as $division) {
            if ($filters['division_id'] !== null && $filters['division_id'] !== $division->id) {
                continue;
            }

            /** @var Collection $divisionTickets */
            $divisionTickets = $ticketsByDivision->get($division->id, collect());
            $divisionBreakdown = $this->computeBreakdown(
                $divisionTickets,
                $ticketTypes,
                $locations,
                $priorities,
                $statuses
            );

            $departmentsData = [];
            $ticketsByDept = $divisionTickets->groupBy(fn ($ticket) => $ticket->department_id ?? 0);

            foreach ($division->departments as $department) {
                if ($filters['department_id'] !== null && $filters['department_id'] !== $department->id) {
                    continue;
                }

                /** @var Collection $deptTickets */
                $deptTickets = $ticketsByDept->get($department->id, collect());
                $deptBreakdown = $this->computeBreakdown(
                    $deptTickets,
                    $ticketTypes,
                    $locations,
                    $priorities,
                    $statuses
                );

                $departmentsData[] = [
                    'id' => $department->id,
                    'name' => $department->name,
                    'total_tickets' => $deptTickets->count(),
                    'rows' => $this->computeDepartmentRows($deptTickets, $ticketTypes, $locations, $priorities),
                    'breakdown' => $deptBreakdown,
                ];
            }

            // Check if there are tickets with no department in this division
            if ($filters['department_id'] === null) {
                /** @var Collection $unassignedDeptTickets */
                $unassignedDeptTickets = $ticketsByDept->get(0, collect());
                if ($unassignedDeptTickets->isNotEmpty()) {
                    $unassignedDeptBreakdown = $this->computeBreakdown(
                        $unassignedDeptTickets,
                        $ticketTypes,
                        $locations,
                        $priorities,
                        $statuses
                    );

                    $departmentsData[] = [
                        'id' => null,
                        'name' => 'General / Unassigned Department',
                        'total_tickets' => $unassignedDeptTickets->count(),
                        'rows' => $this->computeDepartmentRows($unassignedDeptTickets, $ticketTypes, $locations, $priorities),
                        'breakdown' => $unassignedDeptBreakdown,
                    ];
                }
            }

            $divisionsData[] = [
                'id' => $division->id,
                'name' => $division->name,
                'total_tickets' => $divisionTickets->count(),
                'breakdown' => $divisionBreakdown,
                'departments' => $departmentsData,
            ];
        }

        // Check if there are tickets with no division assigned
        if ($filters['division_id'] === null) {
            /** @var Collection $unassignedDivTickets */
            $unassignedDivTickets = $ticketsByDivision->get(0, collect());
            if ($unassignedDivTickets->isNotEmpty()) {
                $unassignedDivBreakdown = $this->computeBreakdown(
                    $unassignedDivTickets,
                    $ticketTypes,
                    $locations,
                    $priorities,
                    $statuses
                );

                $departmentsData = [];
                $ticketsByDept = $unassignedDivTickets->groupBy(fn ($ticket) => $ticket->department_id ?? 0);

                foreach ($ticketsByDept as $deptId => $deptTickets) {
                    $deptName = 'Unassigned Department';
                    if ($deptId > 0) {
                        $foundDept = Department::find($deptId);
                        if ($foundDept) {
                            $deptName = $foundDept->name;
                        }
                    }

                    $departmentsData[] = [
                        'id' => $deptId > 0 ? $deptId : null,
                        'name' => $deptName,
                        'total_tickets' => $deptTickets->count(),
                        'rows' => $this->computeDepartmentRows($deptTickets, $ticketTypes, $locations, $priorities),
                        'breakdown' => $this->computeBreakdown(
                            $deptTickets,
                            $ticketTypes,
                            $locations,
                            $priorities,
                            $statuses
                        ),
                    ];
                }

                $divisionsData[] = [
                    'id' => null,
                    'name' => 'Unassigned Division',
                    'total_tickets' => $unassignedDivTickets->count(),
                    'breakdown' => $unassignedDivBreakdown,
                    'departments' => $departmentsData,
                ];
            }
        }

        return [
            'overall' => $overallBreakdown,
            'divisions' => $divisionsData,
            'total_divisions_count' => count($divisionsData),
            'total_departments_count' => array_sum(array_map(fn ($d) => count($d['departments']), $divisionsData)),
        ];
    }

    /**
     * Compute multi-dimensional table rows (Ticket Type, Location, Priority, Status, Count).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function computeDepartmentRows(
        Collection $tickets,
        Collection $ticketTypes,
        Collection $locations,
        Collection $priorities
    ): array {
        $ticketTypeMap = $ticketTypes->keyBy('id');
        $locationMap = $locations->keyBy('id');
        $priorityMap = $priorities->keyBy('id');

        return $tickets->groupBy(function ($ticket) {
            return implode('__', [
                $ticket->ticket_type_id ?? 0,
                $ticket->location_id ?? 0,
                $ticket->priority_option_id ?? 0,
                $ticket->status ?? 'Valid',
            ]);
        })->map(function ($group) use ($ticketTypeMap, $locationMap, $priorityMap) {
            $first = $group->first();
            $type = $ticketTypeMap->get($first->ticket_type_id);
            $location = $locationMap->get($first->location_id);
            $priority = $priorityMap->get($first->priority_option_id);

            return [
                'ticket_type' => $type?->name ?? 'Unassigned',
                'location' => $location?->name ?? 'Unassigned',
                'priority' => $priority?->name ?? 'Unassigned',
                'status' => $first->status ?? 'Valid',
                'count' => $group->count(),
            ];
        })->values()->all();
    }

    /**
     * Compute breakdown counts and percentages for ticket types, locations, priorities, and statuses.
     *
     * @param  array<int, string>  $statuses
     * @return array<string, mixed>
     */
    protected function computeBreakdown(
        Collection $tickets,
        Collection $ticketTypes,
        Collection $locations,
        Collection $priorities,
        array $statuses
    ): array {
        $total = $tickets->count();

        // 1. Ticket Types
        $typeCounts = $tickets->groupBy('ticket_type_id');
        $typesData = $ticketTypes->map(function ($type) use ($typeCounts, $total) {
            $count = isset($typeCounts[$type->id]) ? $typeCounts[$type->id]->count() : 0;

            return [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        });

        // 2. Locations
        $locCounts = $tickets->groupBy('location_id');
        $locationsData = $locations->map(function ($loc) use ($locCounts, $total) {
            $count = isset($locCounts[$loc->id]) ? $locCounts[$loc->id]->count() : 0;

            return [
                'id' => $loc->id,
                'name' => $loc->name,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        });

        $unassignedLocCount = ($locCounts[''] ?? collect())->count() + ($locCounts[0] ?? collect())->count() + ($locCounts[null] ?? collect())->count();
        if ($unassignedLocCount > 0) {
            $locationsData->push([
                'id' => null,
                'name' => 'Unassigned',
                'count' => $unassignedLocCount,
                'percentage' => $total > 0 ? round(($unassignedLocCount / $total) * 100, 1) : 0,
            ]);
        }

        // 3. Priorities
        $priorityCounts = $tickets->groupBy('priority_option_id');
        $prioritiesData = $priorities->map(function ($prio) use ($priorityCounts, $total) {
            $count = isset($priorityCounts[$prio->id]) ? $priorityCounts[$prio->id]->count() : 0;

            return [
                'id' => $prio->id,
                'name' => $prio->name,
                'level' => $prio->level,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        });

        $unassignedPrioCount = ($priorityCounts[''] ?? collect())->count() + ($priorityCounts[0] ?? collect())->count() + ($priorityCounts[null] ?? collect())->count();
        if ($unassignedPrioCount > 0) {
            $prioritiesData->push([
                'id' => null,
                'name' => 'Unassigned',
                'level' => 0,
                'count' => $unassignedPrioCount,
                'percentage' => $total > 0 ? round(($unassignedPrioCount / $total) * 100, 1) : 0,
            ]);
        }

        // 4. Statuses
        $statusCounts = $tickets->groupBy('status');
        $statusesData = collect($statuses)->map(function ($status) use ($statusCounts, $total) {
            $count = isset($statusCounts[$status]) ? $statusCounts[$status]->count() : 0;

            return [
                'status' => $status,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        });

        return [
            'total' => $total,
            'ticket_types' => $typesData,
            'locations' => $locationsData,
            'priorities' => $prioritiesData,
            'statuses' => $statusesData,
        ];
    }
}
