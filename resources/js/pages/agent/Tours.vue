<script setup lang="ts">
import { h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { agentTours, agentTourDetails } from '@/routes';
import { type BreadcrumbItem, type Column } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Stat } from '@/types/stat';
import { Tour } from '@/types/tour';
import StartCard from '@/components/custom/StartCard.vue';
import DataTable from '@/components/custom/table/DataTable.vue';
import { countNumberOfDays } from '@/composables/countNumberOdDays';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { MoreVertical, Eye } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tours',
        href: agentTours().url,
    },
];

const props = defineProps<{
    responseData: {
        stats: Stat[];
        tours: Tour[];
    };
}>();

const columns: Column[] = [
    {
        key: 'id',
        label: 'ID',
        sortable: true,
        width: '80px',
    },
    {
        key: 'title',
        label: 'Title',
        sortable: true,
    },
    {
        key: 'tour_vehicles_count',
        label: 'Vehicles',
        sortable: true,
        render: (value) => value ?? '-',
    },
    {
        key: 'numberOfSeat',
        label: 'Seats',
        sortable: true,
        render: (value) => value ?? '-',
    },
    {
        key: 'noOfDays',
        label: 'Days',
        sortable: true,
        render: (value, row: Tour) => {
            if (!row || !row.startDate || !row.endDate) return '-';
            return countNumberOfDays(row.startDate, row.endDate);
        },
    },
    {
        key: 'startDate',
        label: 'Start Date',
        sortable: true,
        render: (value) => {
            if (!value) return '-';
            return new Date(value).toLocaleDateString();
        },
    },
    {
        key: 'endDate',
        label: 'End Date',
        sortable: true,
        render: (value) => {
            if (!value) return '-';
            return new Date(value).toLocaleDateString();
        },
    },
    {
        key: 'language',
        label: 'Language',
        sortable: true,
        render: (value) => value ?? '-',
    },
    {
        key: 'created_at',
        label: 'Created At',
        sortable: true,
        render: (value) => {
            if (!value) return '-';
            return new Date(value).toLocaleDateString();
        },
    },
    {
        key: 'actions',
        label: 'Actions',
        sortable: false,
        searchable: false,
        width: '80px',
        align: 'right',
        render: (value, row: Tour) => {
            if (!row || !row.id) return '';
            
            return h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, {
                        default: () => h(Button, {
                            variant: 'ghost',
                            size: 'icon',
                            class: 'h-8 w-8',
                        }, {
                            default: () => h(MoreVertical as any, { class: 'h-4 w-4' }),
                        }),
                    }),
                    h(DropdownMenuContent, { align: 'end' }, {
                        default: () => [
                            h(DropdownMenuItem, {
                                onClick: () => {
                                    router.visit(agentTourDetails(row.id).url);
                                },
                            }, {
                                default: () => [
                                    h(Eye as any, { class: 'mr-2 h-4 w-4' }),
                                    'View Details',
                                ],
                            }),
                        ],
                    }),
                ],
            });
        },
    },
];
</script>

<template>
    <Head title="Tours" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid gap-4 md:grid-cols-4">
                <StartCard
                    v-for="(item, index) in props.responseData.stats"
                    :key="index"
                    :title="item.title"
                    :value="item.value"
                />
            </div>
            <DataTable :data="props.responseData.tours" :columns="columns" />
        </div>
    </AppLayout>
</template>
