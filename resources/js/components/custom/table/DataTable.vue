<script setup lang="ts">
import { ref, computed, watch, defineComponent } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuTrigger,
    DropdownMenuCheckboxItem,
    DropdownMenuLabel,
    DropdownMenuSeparator
} from '@/components/ui/dropdown-menu';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select';
import { 
    ChevronLeft, 
    ChevronRight, 
    ChevronsLeft, 
    ChevronsRight,
    Settings2,
    Search,
    ArrowUpDown,
    ArrowUp,
    ArrowDown
} from 'lucide-vue-next';
import { type Column } from '@/types';

interface Pagination {
    page: number;
    perPage: number;
    total: number;
    totalPages: number;
}

interface Props {
    data: any[];
    columns: Column[];
    pagination?: Pagination;
    loading?: boolean;
    searchable?: boolean;
    sortable?: boolean;
    showColumnToggle?: boolean;
    showPagination?: boolean;
    showPerPageSelector?: boolean;
    perPageOptions?: number[];
    searchPlaceholder?: string;
    emptyMessage?: string;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    searchable: true,
    sortable: true,
    showColumnToggle: true,
    showPagination: true,
    showPerPageSelector: true,
    perPageOptions: () => [10, 25, 50, 100],
    searchPlaceholder: 'Search...',
    emptyMessage: 'No data available'
});

const emit = defineEmits<{
    'update:pagination': [pagination: Pagination];
    'sort': [column: string, direction: 'asc' | 'desc'];
    'search': [query: string];
    'per-page-change': [perPage: number];
}>();

// Reactive state
const searchQuery = ref('');
const visibleColumns = ref<Set<string>>(new Set());
const sortColumn = ref<string>('');
const sortDirection = ref<'asc' | 'desc'>('asc');
const currentPage = ref(1);
const perPage = ref(10);

// Initialize visible columns
watch(() => props.columns, (newColumns: Column[]) => {
    // console.log('Columns changed:', newColumns);
    if (newColumns.length > 0 && visibleColumns.value.size === 0) {
        newColumns.forEach((col: Column) => {
            visibleColumns.value.add(col.key);
            // console.log('Added column to visible:', col.key);
        });
        // console.log('Initial visible columns:', Array.from(visibleColumns.value));
    }
}, { immediate: true });

// Watch for search query changes
watch(searchQuery, () => {
    currentPage.value = 1; // Reset to first page on search
    emit('search', searchQuery.value);
});

// Computed properties
const filteredColumns = computed(() => {
    const filtered = props.columns.filter((col: Column) => visibleColumns.value.has(col.key));
    // console.log('Filtered columns:', filtered.map(col => col.key));
    return filtered;
});

const filteredData = computed(() => {
    let result = [...props.data];
    
    // Apply search filter
    if (searchQuery.value && props.searchable) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((row, index) => {
            return props.columns.some((col: Column) => {
                // Skip searchable check - search all columns by default
                // Only skip if explicitly set to false
                if (col.searchable === false) return false;
                
                const value = col.render ? col.render(row[col.key], row, index) : row[col.key];
                return String(value).toLowerCase().includes(query);
            });
        });
    }
    
    // Apply sorting
    if (sortColumn.value && props.sortable) {
        result.sort((a, b) => {
            const aVal = a[sortColumn.value];
            const bVal = b[sortColumn.value];
            
            if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1;
            if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1;
            return 0;
        });
    }
    
    return result;
});

const paginatedData = computed(() => {
    if (!props.showPagination) {
        return filteredData.value;
    }
    
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredData.value.slice(start, end);
});

const totalPages = computed(() => {
    if (!props.showPagination) return 1;
    return Math.ceil(filteredData.value.length / perPage.value);
});

// Methods
const toggleColumn = (columnKey: string) => {
    // console.log('Toggling column:', columnKey, 'Current state:', visibleColumns.value.has(columnKey));
    if (visibleColumns.value.has(columnKey)) {
        visibleColumns.value.delete(columnKey);
    } else {
        visibleColumns.value.add(columnKey);
    }
    // console.log('New visible columns:', Array.from(visibleColumns.value));
};

const handleSort = (columnKey: string) => {
    if (!props.sortable) return;
    
    if (sortColumn.value === columnKey) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = columnKey;
        sortDirection.value = 'asc';
    }
    
    emit('sort', sortColumn.value, sortDirection.value);
};

const handleSearch = () => {
    emit('search', searchQuery.value);
    currentPage.value = 1; // Reset to first page on search
};

const handlePerPageChange = (newPerPage: unknown) => {
    if (newPerPage === null || newPerPage === undefined) return;
    const numValue = typeof newPerPage === 'number' 
        ? newPerPage 
        : typeof newPerPage === 'string' 
        ? parseInt(newPerPage, 10) 
        : typeof newPerPage === 'bigint'
        ? Number(newPerPage)
        : Number(newPerPage);
    if (isNaN(numValue)) return;
    perPage.value = numValue;
    currentPage.value = 1; // Reset to first page
    emit('per-page-change', numValue);
};

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const getSortIcon = (columnKey: string) => {
    if (sortColumn.value !== columnKey) {
        return ArrowUpDown;
    }
    return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
};

const renderCellValue = (column: Column, row: any, index: number) => {
    const value = row[column.key];
    return column.render ? column.render(value, row, index) : value;
};

// Component to render cell content (supports both VNodes and primitives)
const RenderCell = defineComponent({
    props: {
        render: {
            type: Function,
            required: true
        }
    },
    setup(props: { render: () => any }) {
        // Call render function in the render function to ensure reactivity
        return () => {
        const result = props.render();
        if (result && typeof result === 'object' && 'type' in result) {
                return result;
        }
            return String(result ?? '');
        };
    }
});
</script>

<template>
    <div class="w-full space-y-4">
        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <!-- Search -->
            <div v-if="searchable" class="relative flex-1 max-w-sm">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                    v-model="searchQuery"
                    :placeholder="searchPlaceholder"
                    class="pl-10"
                    @keyup="handleSearch"
                    @input="handleSearch"
                />
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center gap-2 ml-auto">
                <!-- Column Toggle -->
                <DropdownMenu v-if="showColumnToggle">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm">
                            <Settings2 class="h-4 w-4 mr-2" />
                            Columns
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuLabel>Toggle columns</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <div
                            v-for="column in props.columns"
                            :key="column.key"
                            class="flex items-center gap-3 px-3 py-2 text-sm cursor-pointer hover:bg-accent hover:text-accent-foreground rounded-md transition-colors duration-150"
                            @click="toggleColumn(column.key)"
                        >
                            <div class="relative">
                                <input
                                    type="checkbox"
                                    :checked="visibleColumns.has(column.key)"
                                    class="sr-only"
                                    readonly
                                />
                                <div 
                                    class="h-4 w-4 border-2 rounded-sm flex items-center justify-center transition-all duration-150"
                                    :class="visibleColumns.has(column.key) 
                                        ? 'bg-primary border-primary text-primary-foreground' 
                                        : 'border-muted-foreground/30 hover:border-muted-foreground/50'"
                                >
                                    <svg 
                                        v-if="visibleColumns.has(column.key)"
                                        class="h-3 w-3" 
                                        fill="currentColor" 
                                        viewBox="0 0 20 20"
                                    >
                                        <path 
                                            fill-rule="evenodd" 
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" 
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <span class="select-none">{{ column.label }}</span>
                        </div>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead
                            v-for="column in filteredColumns"
                            :key="column.key"
                            :class="[
                                'select-none',
                                column.align === 'center' ? 'text-center' : '',
                                column.align === 'right' ? 'text-right' : '',
                                column.sortable ? 'cursor-pointer hover:bg-muted/50' : ''
                            ]"
                            :style="{ width: column.width }"
                            @click="column.sortable ? handleSort(column.key) : null"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ column.label }}</span>
                                <component
                                    v-if="column.sortable"
                                    :is="getSortIcon(column.key)"
                                    class="h-4 w-4 opacity-50"
                                />
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading">
                        <TableCell :colspan="filteredColumns.length" class="text-center py-8">
                            <div class="flex items-center justify-center gap-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
                                Loading...
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-else-if="paginatedData.length === 0">
                        <TableCell :colspan="filteredColumns.length" class="text-center py-8 text-muted-foreground">
                            {{ emptyMessage }}
                        </TableCell>
                    </TableRow>
                    <TableRow v-else v-for="(row, index) in paginatedData" :key="index">
                        <TableCell
                            v-for="column in filteredColumns"
                            :key="column.key"
                            :class="[
                                column.align === 'center' ? 'text-center' : '',
                                column.align === 'right' ? 'text-right' : ''
                            ]"
                        >
                            <RenderCell :render="() => renderCellValue(column, row, index)" />
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div v-if="showPagination && totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Per Page Selector -->
            <div v-if="showPerPageSelector" class="flex items-center gap-2">
                <span class="text-sm text-muted-foreground">Rows per page:</span>
                <Select :model-value="perPage" @update:model-value="handlePerPageChange">
                    <SelectTrigger class="w-20">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="option in perPageOptions" :key="option" :value="option">
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Page Info -->
            <div class="text-sm text-muted-foreground">
                Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, filteredData.length) }} of {{ filteredData.length }} entries
            </div>

            <!-- Pagination Controls -->
            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage === 1"
                    @click="goToPage(1)"
                >
                    <ChevronsLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                
                <!-- Page Numbers -->
                <div class="flex items-center gap-1">
                    <Button
                        v-for="page in Math.min(5, totalPages)"
                        :key="page"
                        variant="outline"
                        size="sm"
                        :class="{ 'bg-primary text-primary-foreground': currentPage === page }"
                        @click="goToPage(page)"
                    >
                        {{ page }}
                    </Button>
                    <span v-if="totalPages > 5" class="px-2 text-sm text-muted-foreground">...</span>
                    <Button
                        v-if="totalPages > 5"
                        variant="outline"
                        size="sm"
                        :class="{ 'bg-primary text-primary-foreground': currentPage === totalPages }"
                        @click="goToPage(totalPages)"
                    >
                        {{ totalPages }}
                    </Button>
                </div>

                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage === totalPages"
                    @click="goToPage(currentPage + 1)"
                >
                    <ChevronRight class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage === totalPages"
                    @click="goToPage(totalPages)"
                >
                    <ChevronsRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
