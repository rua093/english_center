<style>
    .focus-lime:focus { border-color: #a3e635; box-shadow: 0 0 0 4px rgba(163, 230, 53, 0.1); }
    .form-card { background: #ffffff; border-radius: 2.5rem; border: 1px solid #f1f5f9; }
    .section-title { border-left: 6px solid #e11d48; padding-left: 1rem; margin-bottom: 2rem; font-weight: 900; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em; }
    .sub-title { font-weight: 800; color: #334155; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.02em; }
    .repeater-box { background: #f8fafc; border-radius: 1.5rem; padding: 1.5rem; margin-bottom: 1rem; border: 1px solid #e2e8f0; position: relative; }
    .btn-add { background: #a3e635; color: #166534; font-weight: 800; padding: 0.5rem 1.5rem; border-radius: 99px; font-size: 0.875rem; transition: all 0.3s; }
    .btn-add:hover { background: #84cc16; transform: scale(1.05); }
    .btn-remove { position: absolute; top: 1rem; right: 1rem; color: #ef4444; cursor: pointer; transition: transform 0.2s; }
    .btn-remove:hover { transform: scale(1.2); }
    .location-label {
        display: block;
        margin-bottom: 0.5rem;
        margin-left: 0.25rem;
        font-size: 0.7rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #64748b;
    }
    .location-select {
        width: 100%;
        min-height: 3.5rem;
        padding: 0.9rem 3rem 0.9rem 1.25rem;
        border-radius: 1.25rem;
        border: 1px solid #e2e8f0;
        background-color: #fff;
        color: #0f172a;
        font-weight: 800;
        line-height: 1.25;
        appearance: none;
        background-image: linear-gradient(45deg, transparent 50%, #94a3b8 50%), linear-gradient(135deg, #94a3b8 50%, transparent 50%);
        background-position: calc(100% - 18px) calc(50% - 3px), calc(100% - 12px) calc(50% - 3px);
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }
    .location-select:hover:not(:disabled) {
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    .location-select:focus {
        outline: none;
        border-color: #a3e635;
        box-shadow: 0 0 0 4px rgba(163, 230, 53, 0.12);
    }
    .location-select:disabled {
        background-color: #f8fafc;
        color: #94a3b8;
        cursor: not-allowed;
        opacity: 1;
        background-image: linear-gradient(45deg, transparent 50%, #cbd5e1 50%), linear-gradient(135deg, #cbd5e1 50%, transparent 50%);
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        /* =========================================
           1. AUTO FORMAT TIỀN TỆ (Mức lương)
           ========================================= */
        const salaryInput = document.getElementById('salary_input');
        if (salaryInput) {
            salaryInput.addEventListener('input', function(e) {
                const value = e.target.value.replace(/\D/g, '');
                if (value !== '') {
                    e.target.value = new Intl.NumberFormat('en-US').format(value);
                }
            });
        }

        /* =========================================
           2. REPEATER FUNCTIONS
           ========================================= */
        window.addExperience = function addExperience() {
            const html = `
            <div class="repeater-box animate-fade-in-down mt-4">
                <i class="fa-solid fa-circle-xmark text-xl btn-remove" onclick="this.parentElement.remove()"></i>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <input type="text" name="exp_company[]" placeholder="Công ty / Tổ chức đã làm việc" class="px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm">
                    <input type="text" name="exp_position[]" placeholder="Vị trí đảm nhiệm" class="px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm">
                </div>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div class="relative">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-black text-slate-400 uppercase">Từ ngày</label>
                        <input type="date" name="exp_start[]" class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm text-slate-600">
                    </div>
                    <div class="relative">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-black text-slate-400 uppercase">Đến ngày</label>
                        <input type="date" name="exp_end[]" class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm text-slate-600">
                    </div>
                </div>
                <textarea name="exp_detail[]" placeholder="Mô tả công việc chính..." class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm h-24 resize-none"></textarea>
            </div>`;
            const experienceContainer = document.getElementById('experience-container');
            if (experienceContainer) {
                experienceContainer.insertAdjacentHTML('beforeend', html);
            }
        };

        window.addProSkill = function addProSkill() {
            const html = `
                <div class="relative group animate-fade-in-down">
                    <input type="text" name="skill_pro[]" placeholder="Nhập kỹ năng chuyên môn..." class="w-full px-5 py-3.5 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm">
                    <button type="button" onclick="this.parentElement.remove()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-circle-xmark text-lg"></i>
                    </button>
                </div>`;
            const proSkillContainer = document.getElementById('pro-skill-container');
            if (proSkillContainer) {
                proSkillContainer.insertAdjacentHTML('beforeend', html);
            }
        };

        window.addOtherSkill = function addOtherSkill() {
            const html = `
            <div class="relative group animate-fade-in-down">
                <input type="text" name="skill_other[]" placeholder="Nhập kỹ năng khác..." class="w-full px-5 py-3.5 rounded-xl bg-white border border-slate-200 outline-none focus-lime font-bold text-sm">
                <button type="button" onclick="this.parentElement.remove()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-circle-xmark text-lg"></i>
                </button>
            </div>`;
            const otherSkillContainer = document.getElementById('other-skill-container');
            if (otherSkillContainer) {
                otherSkillContainer.insertAdjacentHTML('beforeend', html);
            }
        };

          /* =========================================
              3. ĐỊA CHỈ TỪ API
              ========================================= */
        const addressData = <?php echo json_encode(require __DIR__ . '/../partials/province.php', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const provinceSelect = document.getElementById('province');
        const districtSelect = document.getElementById('district');
        const wardSelect = document.getElementById('ward');

        if (!provinceSelect || !districtSelect || !wardSelect) {
            return;
        }

        [provinceSelect, districtSelect, wardSelect].forEach((selectElement) => {
            selectElement.classList.add('location-select');
        });

        const resetSelect = (selectElement, placeholder, disabled = true) => {
            selectElement.innerHTML = `<option value="">${placeholder}</option>`;
            selectElement.disabled = disabled;
            selectElement.classList.add('bg-slate-50', 'cursor-not-allowed');
            selectElement.classList.remove('bg-white');
        };

        const enableSelect = (selectElement) => {
            selectElement.disabled = false;
            selectElement.classList.remove('cursor-not-allowed', 'bg-slate-50');
            selectElement.classList.add('bg-white');
        };

        resetSelect(districtSelect, 'Chọn Loại đơn vị');
        resetSelect(wardSelect, 'Chọn Phường/Xã');

        const provincePlaceholder = provinceSelect.querySelector('option[value=""]')?.textContent || 'Chọn Tỉnh/Thành';
        provinceSelect.innerHTML = `<option value="">${provincePlaceholder}</option>`;
        addressData.provinces.forEach((province) => {
            provinceSelect.add(new Option(province.name, String(province.code)));
        });

        provinceSelect.addEventListener('change', function() {
            const selectedProvince = addressData.provinces.find((province) => String(province.code) === this.value);

            resetSelect(districtSelect, 'Chọn Loại đơn vị');
            resetSelect(wardSelect, 'Chọn Phường/Xã');

            if (!selectedProvince) {
                return;
            }

            enableSelect(districtSelect);
            districtSelect.innerHTML = '<option value="">Chọn Loại đơn vị</option>';
            selectedProvince.districts.forEach((district) => {
                districtSelect.add(new Option(district.name, String(district.code)));
            });
        });

        districtSelect.addEventListener('change', function() {
            const selectedProvince = addressData.provinces.find((province) => String(province.code) === provinceSelect.value);
            const selectedDistrict = selectedProvince?.districts?.find((district) => String(district.code) === this.value);

            resetSelect(wardSelect, 'Chọn Phường/Xã');

            if (!selectedDistrict) {
                return;
            }

            enableSelect(wardSelect);
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            selectedDistrict.wards.forEach((ward) => {
                wardSelect.add(new Option(ward.name, String(ward.code)));
            });
        });
    });
</script>
