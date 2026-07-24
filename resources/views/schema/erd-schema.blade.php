@extends('layouts.master')

@section('title', 'Database Schema Explorer')
@section('subtitle', 'ER Diagram Mapping & Live Analytics')

@push('styles')
    <style>
        .schema-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }
        .table-card-unified {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .table-card-unified:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }
        .table-header-custom {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .live-count-badge {
            background-color: #235c2b;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 9999px;
            box-shadow: 0 2px 4px rgba(35, 92, 43, 0.2);
        }
        .table-body-custom {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .column-badge {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            margin: 2px;
        }
        .column-badge-type {
            font-size: 9px;
            color: #94a3b8;
            margin-left: 4px;
            font-style: italic;
        }
    </style>
@endpush

@section('topbar-actions')
    <div class="flex items-center gap-2">
        <a href="/api/suppliers" target="_blank" class="top-bar-btn bg-emerald-600/80 hover:bg-emerald-700/90 border-emerald-500/30 flex items-center gap-1">
            <i data-lucide="api" class="w-4 h-4"></i>
            <span>Suppliers API</span>
        </a>
        <a href="/api/purchase-orders" target="_blank" class="top-bar-btn bg-blue-600/80 hover:bg-blue-700/90 border-blue-500/30 flex items-center gap-1">
            <i data-lucide="api" class="w-4 h-4"></i>
            <span>PO API</span>
        </a>
    </div>
@endsection

@section('content')
<div x-data="{ activeTable: null, showSampleModal: false }">

    <div class="mb-8 p-6 bg-gradient-to-r from-emerald-850 to-green-900 rounded-2xl text-white shadow-soft relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at top right, rgba(255,255,255,0.4), transparent 60%);"></div>
        <div class="relative z-10">
            <h3 class="text-lg font-black uppercase tracking-wider mb-2">ER Diagram Database Alignment</h3>
            <p class="text-sm text-emerald-100 max-w-3xl leading-relaxed">
                This explorer provides live mapping, column listings, row analytics, and sample data direct from the relational database for all 10 core tables defined in the procurement model schema.
            </p>
        </div>
    </div>

    <!-- Live Schema Grid -->
    <div class="schema-grid">
        @foreach ($schemaData as $key => $table)
            <div class="table-card-unified">
                <div class="table-header-custom">
                    <div class="flex items-center gap-2">
                        <i data-lucide="database" class="w-4.5 h-4.5 text-slate-500"></i>
                        <h4 class="text-sm font-black text-slate-800">{{ $table['name'] }}</h4>
                    </div>
                    <span class="live-count-badge">{{ $table['count'] }} rows</span>
                </div>
                <div class="table-body-custom">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Description</span>
                        <p class="text-xs text-slate-655 leading-relaxed mb-4">{{ $table['description'] }}</p>

                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-2">Columns</span>
                        <div class="flex flex-wrap gap-1 mb-4">
                            @foreach ($table['columns'] as $col)
                                <span class="column-badge">
                                    {{ $col['name'] }}
                                    <span class="column-badge-type">({{ $col['type'] }})</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex gap-2">
                        <button @click="activeTable = @json($table); showSampleModal = true" class="flex-1 py-2 text-center text-xs font-bold bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl transition-colors border border-slate-200/60">
                            Inspect Data
                        </button>
                        <a href="/api/{{ str_replace('_', '-', $key) }}" target="_blank" class="px-3 py-2 text-center text-xs font-bold bg-green-50 hover:bg-green-100 text-green-800 rounded-xl transition-colors border border-green-200/50 flex items-center justify-center">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Sample Data Inspector Modal -->
    <div x-show="showSampleModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[150] flex items-center justify-center p-4" x-cloak style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col" @click.away="showSampleModal = false">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <div>
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider" x-text="activeTable ? 'Data Inspector: ' + activeTable.name : ''">Inspect</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5" x-text="activeTable ? 'Table: ' + activeTable.table : ''"></p>
                </div>
                <button @click="showSampleModal = false" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Content (Table data) -->
            <div class="p-6 overflow-auto flex-grow">
                <template x-if="activeTable && activeTable.samples && activeTable.samples.length > 0">
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider">
                                <tr>
                                    <template x-for="col in activeTable.columns">
                                        <th class="px-4 py-3" x-text="col.name"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-800">
                                <template x-for="row in activeTable.samples">
                                    <tr class="hover:bg-slate-50/50">
                                        <template x-for="col in activeTable.columns">
                                            <td class="px-4 py-3 font-mono whitespace-nowrap" x-text="row[col.name] !== null ? row[col.name] : 'NULL'"></td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>

                <template x-if="activeTable && (!activeTable.samples || activeTable.samples.length === 0)">
                    <div class="text-center py-12 text-slate-400">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                        <p class="text-sm font-bold">This table is currently empty.</p>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-slate-200 flex justify-end bg-slate-50">
                <button @click="showSampleModal = false" class="px-4 py-2 text-xs font-bold bg-slate-250 hover:bg-slate-300 text-slate-800 rounded-xl transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
