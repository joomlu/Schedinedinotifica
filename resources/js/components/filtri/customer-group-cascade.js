import { initOnce } from '../../core/once';

function buildConfig(select) {
    const placeholder = select.getAttribute('data-placeholder') || 'Seleziona...';
    const parentModal = select.closest('.modal');
    const cfg = {
        theme: 'bootstrap-5',
        width: '100%',
        dropdownAutoWidth: true,
        language: 'it',
        placeholder,
        minimumResultsForSearch: 0,
        minimumInputLength: 0,
        closeOnSelect: true,
        allowClear: false,
        multiple: false,
        tags: false,
        tokenSeparators: [],
    };

    if (parentModal) {
        cfg.dropdownParent = window.jQuery(parentModal);
    }

    return cfg;
}

function mountSelect(select, value = '') {
    if (!select || !window.jQuery) return;
    const $select = window.jQuery(select);

    if ($select.data('select2')) {
        $select.select2('destroy');
    }

    $select.select2(buildConfig(select));
    $select.val(value || '').trigger('change.select2');
}

function getNodes(wrapper) {
    return {
        group: wrapper.querySelector('[data-role="customer-group-l1"]'),
        subgroup: wrapper.querySelector('[data-role="customer-group-l2"]'),
        subgroup1: wrapper.querySelector('[data-role="customer-group-l3"]'),
    };
}

function renderOptions(select, options, selectedValue, buildOption) {
    select.innerHTML = '';
    let hasSelected = false;

    options.forEach((item) => {
        const option = buildOption(item);
        if (option.value === selectedValue) {
            option.selected = true;
            hasSelected = true;
        }
        select.appendChild(option);
    });

    if (!hasSelected && select.options.length > 0) {
        select.selectedIndex = 0;
    }

    mountSelect(select, hasSelected ? selectedValue : '');
}

export function initCustomerGroupCascade(root = document) {
    const scope = root || document;
    const wrappers = scope.querySelectorAll ? scope.querySelectorAll('[data-ui="customer-group-cascade"]') : [];

    wrappers.forEach((wrapper) => {
        if (!initOnce(wrapper, 'customer-group-cascade')) return;

        const { group, subgroup, subgroup1 } = getNodes(wrapper);
        if (!group || !subgroup || !subgroup1 || !window.jQuery) return;

        const subgroupOptions = Array.from(subgroup.options).map((option) => ({
            value: option.value,
            text: option.textContent,
            groupId: option.dataset.groupId || '',
            parentId: option.dataset.parentId || '',
        }));

        const subgroup1Options = Array.from(subgroup1.options).map((option) => ({
            value: option.value,
            text: option.textContent,
            parentId: option.dataset.parentId || '',
        }));

        const applySubgroup1Filter = () => {
            const selectedSubgroup = subgroup.options[subgroup.selectedIndex] || null;
            const selectedSubgroupId = selectedSubgroup ? (selectedSubgroup.dataset.groupId || '') : '';
            const currentSubgroup1 = subgroup1.value;

            const filtered = subgroup1Options.filter((item) => {
                if (item.value === '') return true;
                return selectedSubgroupId !== '' && item.parentId === selectedSubgroupId;
            });

            renderOptions(subgroup1, filtered, currentSubgroup1, (item) => {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.text;
                if (item.parentId) option.dataset.parentId = item.parentId;
                return option;
            });
        };

        const applySubgroupFilter = () => {
            const selectedGroup = group.options[group.selectedIndex] || null;
            const selectedGroupId = selectedGroup ? (selectedGroup.dataset.groupId || '') : '';
            const currentSubgroup = subgroup.value;

            const filtered = subgroupOptions.filter((item) => {
                if (item.value === '') return true;
                return selectedGroupId !== '' && item.parentId === selectedGroupId;
            });

            renderOptions(subgroup, filtered, currentSubgroup, (item) => {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.text;
                if (item.groupId) option.dataset.groupId = item.groupId;
                if (item.parentId) option.dataset.parentId = item.parentId;
                return option;
            });

            applySubgroup1Filter();
        };

        mountSelect(group, group.value);
        mountSelect(subgroup, subgroup.value);
        mountSelect(subgroup1, subgroup1.value);

        window.jQuery(group).on('change.customerGroupCascade', () => {
            applySubgroupFilter();
        });

        window.jQuery(subgroup).on('change.customerGroupCascade', () => {
            applySubgroup1Filter();
        });

        applySubgroupFilter();
    });
}
