const API_BASE = 'https://provinces.open-api.vn/api/v1';

const responseCache = new Map();

const fetchJson = async (url) => {
    if (responseCache.has(url)) {
        return responseCache.get(url);
    }

    const promise = fetch(url, {
        headers: {
            Accept: 'application/json',
        },
    }).then(async (response) => {
        if (!response.ok) {
            throw new Error('Không thể tải dữ liệu địa giới.');
        }

        return response.json();
    });

    responseCache.set(url, promise);

    return promise;
};

const normalizeName = (value = '') => value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/^(tinh|thanh pho|tp\.?|quan|huyen|thi xa|phuong|xa|thi tran)\s+/i, '')
    .replace(/[^a-z0-9]/g, '');

const findDivision = (items, expectedName) => {
    if (!expectedName) {
        return null;
    }

    const expected = normalizeName(expectedName);

    return items.find((item) => normalizeName(item.name) === expected)
        ?? items.find((item) => normalizeName(item.name).includes(expected))
        ?? null;
};

const setOptions = (select, items, placeholder) => {
    const fragment = document.createDocumentFragment();
    const placeholderOption = document.createElement('option');

    placeholderOption.value = '';
    placeholderOption.textContent = placeholder;
    fragment.appendChild(placeholderOption);

    items.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.name;
        option.textContent = item.name;
        option.dataset.code = String(item.code);
        fragment.appendChild(option);
    });

    select.replaceChildren(fragment);
    select.disabled = false;
};

const setLoading = (select, text) => {
    select.disabled = true;
    select.replaceChildren();

    const option = document.createElement('option');
    option.value = '';
    option.textContent = text;
    select.appendChild(option);
};

const initializeAddressSelector = async (root) => {
    const provinceSelect = root.querySelector('[data-vn-province]');
    const districtSelect = root.querySelector('[data-vn-district]');
    const wardSelect = root.querySelector('[data-vn-ward]');
    const status = root.querySelector('[data-vn-address-status]');
    const manualButton = root.querySelector('[data-vn-address-manual-toggle]');
    const selectFields = root.querySelector('[data-vn-address-select-fields]');
    const manualFields = root.querySelector('[data-vn-address-manual-fields]');

    if (!provinceSelect || !districtSelect || !wardSelect) {
        return;
    }

    let provinces = [];
    let districts = [];
    let wards = [];
    let manualMode = false;

    const manualInputs = {
        province: manualFields?.querySelector('[data-vn-manual-province]'),
        district: manualFields?.querySelector('[data-vn-manual-district]'),
        ward: manualFields?.querySelector('[data-vn-manual-ward]'),
    };

    const showStatus = (message, type = 'info') => {
        if (!status) {
            return;
        }

        status.textContent = message;
        status.dataset.type = type;
        status.hidden = message === '';
    };

    const toggleManualMode = (force = null) => {
        manualMode = force ?? !manualMode;

        if (selectFields) {
            selectFields.hidden = manualMode;
        }

        if (manualFields) {
            manualFields.hidden = !manualMode;
        }

        [provinceSelect, districtSelect, wardSelect].forEach((select) => {
            if (manualMode) {
                select.removeAttribute('name');
            } else {
                select.name = select.dataset.fieldName;
            }
        });

        Object.entries(manualInputs).forEach(([key, input]) => {
            if (!input) {
                return;
            }

            if (manualMode) {
                input.name = key;
                input.required = true;
            } else {
                input.removeAttribute('name');
                input.required = false;
            }
        });

        if (manualButton) {
            manualButton.textContent = manualMode
                ? 'Quay lại chọn từ danh sách'
                : 'Nhập địa chỉ hành chính thủ công';
        }
    };

    manualButton?.addEventListener('click', () => {
        if (!manualMode) {
            manualInputs.province.value = provinceSelect.value;
            manualInputs.district.value = districtSelect.value;
            manualInputs.ward.value = wardSelect.value;
        }

        toggleManualMode();
    });

    const loadDistricts = async (provinceCode, expectedName = '') => {
        setLoading(districtSelect, 'Đang tải Quận/Huyện...');
        setLoading(wardSelect, 'Chọn Quận/Huyện trước');

        if (!provinceCode) {
            setOptions(districtSelect, [], 'Chọn Quận/Huyện');
            districtSelect.disabled = true;
            return null;
        }

        const province = await fetchJson(
            `${API_BASE}/p/${provinceCode}?depth=2`
        );

        districts = province.districts ?? [];
        setOptions(districtSelect, districts, 'Chọn Quận/Huyện');

        const selected = findDivision(districts, expectedName);

        if (selected) {
            districtSelect.value = selected.name;
        }

        return selected;
    };

    const loadWards = async (districtCode, expectedName = '') => {
        setLoading(wardSelect, 'Đang tải Phường/Xã...');

        if (!districtCode) {
            setOptions(wardSelect, [], 'Chọn Phường/Xã');
            wardSelect.disabled = true;
            return null;
        }

        const district = await fetchJson(
            `${API_BASE}/d/${districtCode}?depth=2`
        );

        wards = district.wards ?? [];
        setOptions(wardSelect, wards, 'Chọn Phường/Xã');

        const selected = findDivision(wards, expectedName);

        if (selected) {
            wardSelect.value = selected.name;
        }

        return selected;
    };

    const setLocation = async ({ province = '', district = '', ward = '' }) => {
        try {
            showStatus('Đang đồng bộ địa chỉ hành chính...');

            const selectedProvince = findDivision(provinces, province);

            if (!selectedProvince) {
                provinceSelect.value = '';
                setOptions(districtSelect, [], 'Chọn Quận/Huyện');
                setOptions(wardSelect, [], 'Chọn Phường/Xã');

                if (province || district || ward) {
                    manualInputs.province.value = province;
                    manualInputs.district.value = district;
                    manualInputs.ward.value = ward;
                    toggleManualMode(true);
                    showStatus(
                        'Địa chỉ cũ không khớp danh mục. Hệ thống đã chuyển sang nhập thủ công.',
                        'warning'
                    );
                } else {
                    showStatus('');
                }

                return;
            }

            toggleManualMode(false);
            provinceSelect.value = selectedProvince.name;

            const selectedDistrict = await loadDistricts(
                selectedProvince.code,
                district
            );

            if (!selectedDistrict) {
                showStatus('Vui lòng chọn Quận/Huyện.', 'info');
                return;
            }

            await loadWards(selectedDistrict.code, ward);
            showStatus('Đã đồng bộ địa chỉ.', 'success');
        } catch (error) {
            manualInputs.province.value = province || provinceSelect.value;
            manualInputs.district.value = district || districtSelect.value;
            manualInputs.ward.value = ward || wardSelect.value;
            toggleManualMode(true);
            showStatus(
                'Không tải được danh mục địa giới. Bạn vẫn có thể nhập thủ công.',
                'error'
            );
        }
    };

    provinceSelect.addEventListener('change', async () => {
        const selectedOption = provinceSelect.selectedOptions[0];
        const provinceCode = selectedOption?.dataset.code ?? '';

        try {
            showStatus('Đang tải Quận/Huyện...');
            await loadDistricts(provinceCode);
            showStatus('Vui lòng chọn Quận/Huyện.');
        } catch (error) {
            toggleManualMode(true);
            showStatus(
                'Không tải được Quận/Huyện. Hãy nhập thủ công.',
                'error'
            );
        }
    });

    districtSelect.addEventListener('change', async () => {
        const selectedOption = districtSelect.selectedOptions[0];
        const districtCode = selectedOption?.dataset.code ?? '';

        try {
            showStatus('Đang tải Phường/Xã...');
            await loadWards(districtCode);
            showStatus('Vui lòng chọn Phường/Xã.');
        } catch (error) {
            toggleManualMode(true);
            showStatus(
                'Không tải được Phường/Xã. Hãy nhập thủ công.',
                'error'
            );
        }
    });

    wardSelect.addEventListener('change', () => {
        showStatus(
            wardSelect.value ? 'Địa chỉ hành chính đã đầy đủ.' : '',
            'success'
        );
    });

    try {
        setLoading(provinceSelect, 'Đang tải Tỉnh/Thành phố...');
        setLoading(districtSelect, 'Chọn Tỉnh/Thành phố trước');
        setLoading(wardSelect, 'Chọn Quận/Huyện trước');
        showStatus('Đang tải danh mục địa giới...');

        provinces = await fetchJson(`${API_BASE}/p/`);
        setOptions(provinceSelect, provinces, 'Chọn Tỉnh/Thành phố');

        await setLocation({
            province: root.dataset.initialProvince ?? '',
            district: root.dataset.initialDistrict ?? '',
            ward: root.dataset.initialWard ?? '',
        });
    } catch (error) {
        toggleManualMode(true);
        showStatus(
            'Không tải được danh mục địa giới. Bạn vẫn có thể nhập thủ công.',
            'error'
        );
    }

    root.addressSelector = {
        setLocation,
    };
};

document.addEventListener('DOMContentLoaded', () => {
    document
        .querySelectorAll('[data-vietnam-address]')
        .forEach((root) => {
            initializeAddressSelector(root);
        });

    document
        .querySelectorAll('[data-address-selector]')
        .forEach((selector) => {
            const root = selector.closest('form')
                ?.querySelector('[data-vietnam-address]');

            if (!root) {
                return;
            }

            const fields = {
                name: document.getElementById('checkout-name'),
                phone: document.getElementById('checkout-phone'),
                address: document.getElementById('checkout-address'),
            };

            selector.addEventListener('change', async () => {
                const option = selector.selectedOptions[0];

                if (!option?.value) {
                    return;
                }

                Object.entries(fields).forEach(([key, input]) => {
                    if (input) {
                        input.value = option.dataset[key] ?? '';
                    }
                });

                await root.addressSelector?.setLocation({
                    province: option.dataset.province ?? '',
                    district: option.dataset.district ?? '',
                    ward: option.dataset.ward ?? '',
                });
            });
        });
});
