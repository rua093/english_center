<style>
    .focus-lime:focus { border-color: #a3e635; box-shadow: 0 0 0 4px rgba(163, 230, 53, 0.1); }
    .form-card { background: #ffffff; border-radius: 2rem; border: 1px solid #e2e8f0; }
    .section-header { border-bottom: 2px dashed #f1f5f9; padding-bottom: 1.25rem; margin-bottom: 1.5rem; }
    .section-title { font-weight: 900; color: #1e293b; text-transform: uppercase; display: flex; align-items: center; gap: 0.75rem; }
    .title-number { background: #e11d48; color: white; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; font-size: 1rem; }
    
    /* Custom Checkbox/Radio Styling */
    .custom-option { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; border-radius: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s; }
    .custom-option:hover { border-color: #a3e635; background: #f7fee7; }
    .custom-option input:checked + span { font-weight: 800; color: #e11d48; }
    .custom-option:has(input:checked) { border-color: #e11d48; background: #fff1f2; box-shadow: 0 4px 6px -1px rgba(225, 29, 72, 0.1); }
</style>

<main class="py-12 md:py-16 font-jakarta">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <div class="mb-10 text-center bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-rose-600"></div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-left flex items-center gap-4">
                    <img src="assets/images/logo_remove.png" alt="Nhuệ Minh Logo" class="h-16 w-auto">
                    <div>
                        <h2 class="text-xl font-black text-rose-600 tracking-tight">TRUNG TÂM NGOẠI NGỮ NHUỆ MINH</h2>
                        <p class="text-sm font-bold text-slate-500">Hotline: <a href="tel:0899925259" class="text-slate-800 hover:text-rose-600">0899 925 259</a></p>
                    </div>
                </div>
                <div class="text-right">
                    <h1 class="text-2xl md:text-3xl font-black text-slate-800 uppercase">Thông Tin Tư Vấn</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Khảo sát lộ trình học tập</p>
                </div>
            </div>
        </div>

        <form action="save_consultation.php" method="POST" class="space-y-8">
            
            <section class="form-card p-6 md:p-8 shadow-md">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Ngày tư vấn *</label>
                        <input type="date" name="consultation_date" value="<?= date('Y-m-d') ?>" required class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Nhân viên tư vấn *</label>
                        <input type="text" name="consultant_name" required placeholder="Nhập tên nhân viên..." class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                </div>
            </section>

            <section class="form-card p-6 md:p-8 shadow-md">
                <div class="section-header">
                    <h2 class="section-title"><span class="title-number">I</span> Thông tin học sinh</h2>
                </div>
                
                <div class="grid md:grid-cols-12 gap-6 mb-6">
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Họ và tên học sinh *</label>
                        <input type="text" name="student_name" required placeholder="Nguyễn Văn A" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Giới tính</label>
                        <div class="flex gap-4">
                            <label class="custom-option flex-1 justify-center">
                                <input type="radio" name="student_gender" value="Nam" class="w-4 h-4 accent-rose-600">
                                <span class="text-sm font-medium text-slate-600">Nam</span>
                            </label>
                            <label class="custom-option flex-1 justify-center">
                                <input type="radio" name="student_gender" value="Nữ" class="w-4 h-4 accent-rose-600">
                                <span class="text-sm font-medium text-slate-600">Nữ</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Ngày sinh</label>
                        <input type="date" name="student_dob" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Sở thích của bé</label>
                        <input type="text" name="student_hobbies" placeholder="VD: Vẽ, đá bóng, xem hoạt hình..." class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                </div>

                <div class="grid md:grid-cols-12 gap-6 mb-8">
                    <div class="md:col-span-8 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Trường học hiện tại</label>
                        <input type="text" name="student_school" placeholder="Tên trường tiểu học/THCS..." class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Lớp</label>
                        <input type="text" name="student_grade" placeholder="VD: 3A, Lớp 5..." class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-xs font-black text-slate-500 uppercase ml-1">Tính cách của bé</label>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <label class="custom-option">
                            <input type="radio" name="student_personality" value="Hướng nội" class="w-4 h-4 accent-rose-600">
                            <span class="text-sm font-medium text-slate-600">Hướng nội (Nhút nhát, ít nói)</span>
                        </label>
                        <label class="custom-option">
                            <input type="radio" name="student_personality" value="Hướng ngoại" class="w-4 h-4 accent-rose-600">
                            <span class="text-sm font-medium text-slate-600">Hướng ngoại (Hoạt bát)</span>
                        </label>
                        <label class="custom-option">
                            <input type="radio" name="student_personality" value="Trung bình" class="w-4 h-4 accent-rose-600">
                            <span class="text-sm font-medium text-slate-600">Trung bình (Dễ hòa nhập)</span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="form-card p-6 md:p-8 shadow-md">
                <div class="section-header">
                    <h2 class="section-title"><span class="title-number">II</span> Thông tin phụ huynh</h2>
                </div>
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Họ và tên Phụ huynh *</label>
                        <input type="text" name="parent_name" required placeholder="Tên Ba/Mẹ..." class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-500 uppercase ml-1">Số điện thoại Ba</label>
                            <input type="tel" name="father_phone" placeholder="09xx xxx xxx" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-500 uppercase ml-1">Số điện thoại Mẹ</label>
                            <input type="tel" name="mother_phone" placeholder="09xx xxx xxx" class="w-full px-5 py-3.5 rounded-xl bg-slate-50 outline-none focus-lime font-bold text-slate-700">
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <label class="text-xs font-black text-slate-500 uppercase ml-1">Ba mẹ biết Trung tâm thông qua</label>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <label class="custom-option">
                                <input type="checkbox" name="source_channels[]" value="Người quen giới thiệu" class="w-4 h-4 accent-rose-600 rounded">
                                <span class="text-sm font-medium text-slate-600">Có người quen giới thiệu</span>
                            </label>
                            <label class="custom-option">
                                <input type="checkbox" name="source_channels[]" value="Tờ rơi/Quảng cáo" class="w-4 h-4 accent-rose-600 rounded">
                                <span class="text-sm font-medium text-slate-600">Nhận tờ rơi, quảng cáo</span>
                            </label>
                            <label class="custom-option">
                                <input type="checkbox" name="source_channels[]" value="Mạng xã hội" class="w-4 h-4 accent-rose-600 rounded">
                                <span class="text-sm font-medium text-slate-600">Mạng xã hội (Facebook, Zalo...)</span>
                            </label>
                            <div class="relative group">
                                <div class="custom-option focus-within:border-rose-600 focus-within:bg-rose-50">
                                    <input type="checkbox" id="source_other_check" name="source_channels[]" value="Khác" class="w-4 h-4 accent-rose-600 rounded">
                                    <span class="text-sm font-medium text-slate-600 whitespace-nowrap">Khác:</span>
                                    <input type="text" name="source_other_detail" placeholder="Vui lòng ghi rõ..." class="w-full bg-transparent border-b border-slate-300 outline-none text-sm font-bold text-slate-700 ml-2 focus:border-rose-600 placeholder:font-normal">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="form-card p-6 md:p-8 shadow-md">
                <div class="section-header">
                    <h2 class="section-title"><span class="title-number">III</span> Mục tiêu học tập</h2>
                </div>
                
                <div class="grid lg:grid-cols-2 gap-10">
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-rose-600 uppercase mb-3">1. Trình độ tiếng Anh hiện tại</h3>
                        <div class="space-y-3">
                            <label class="custom-option">
                                <input type="radio" name="current_level" value="Chưa tiếp xúc" class="w-4 h-4 accent-rose-600">
                                <span class="text-sm font-medium text-slate-600">Chưa tiếp xúc với tiếng Anh</span>
                            </label>
                            <label class="custom-option">
                                <input type="radio" name="current_level" value="Biết từ vựng cơ bản" class="w-4 h-4 accent-rose-600">
                                <span class="text-sm font-medium text-slate-600">Biết một số từ vựng cơ bản</span>
                            </label>
                            <label class="custom-option">
                                <input type="radio" name="current_level" value="Nghe hiểu được, phản xạ chậm" class="w-4 h-4 accent-rose-600">
                                <span class="text-sm font-medium text-slate-600">Nghe hiểu được nhưng phản xạ chậm</span>
                            </label>
                            <label class="custom-option">
                                <input type="radio" name="current_level" value="Có nền tảng, giao tiếp cơ bản" class="w-4 h-4 accent-rose-600">
                                <span class="text-sm font-medium text-slate-600">Đã có nền tảng vững, giao tiếp cơ bản</span>
                            </label>
                            <label class="custom-option">
                                <input type="radio" name="current_level" value="Học tốt, muốn nâng cao" class="w-4 h-4 accent-rose-600">
                                <span class="text-sm font-medium text-slate-600">Học tốt tiếng Anh nhưng muốn nâng cao</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="space-y-4">
                            <h3 class="text-sm font-black text-rose-600 uppercase mb-3">2. Thời gian bé có thể học</h3>
                            <div class="grid grid-cols-3 gap-3 mb-3">
                                <label class="custom-option justify-center"><input type="checkbox" name="available_shifts[]" value="Sáng" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600">Sáng</span></label>
                                <label class="custom-option justify-center"><input type="checkbox" name="available_shifts[]" value="Chiều" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600">Chiều</span></label>
                                <label class="custom-option justify-center"><input type="checkbox" name="available_shifts[]" value="Tối" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600">Tối</span></label>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="custom-option justify-center"><input type="checkbox" name="available_days[]" value="Ngày trong tuần" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600">Ngày trong tuần</span></label>
                                <label class="custom-option justify-center"><input type="checkbox" name="available_days[]" value="Cuối tuần" class="w-4 h-4 accent-rose-600"><span class="text-sm text-slate-600">Cuối tuần</span></label>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-sm font-black text-rose-600 uppercase mb-3">3. Phụ huynh mong muốn điều gì?</h3>
                            <div class="space-y-3">
                                <label class="custom-option">
                                    <input type="checkbox" name="parent_expectations[]" value="Tiến bộ giao tiếp" class="w-4 h-4 accent-rose-600 rounded">
                                    <span class="text-sm font-medium text-slate-600">Bé tiến bộ rõ rệt trong giao tiếp</span>
                                </label>
                                <label class="custom-option">
                                    <input type="checkbox" name="parent_expectations[]" value="Điểm cao tại trường" class="w-4 h-4 accent-rose-600 rounded">
                                    <span class="text-sm font-medium text-slate-600">Bé đạt điểm cao trong kỳ thi tại trường</span>
                                </label>
                                <label class="custom-option">
                                    <input type="checkbox" name="parent_expectations[]" value="Lộ trình chứng chỉ quốc tế" class="w-4 h-4 accent-rose-600 rounded">
                                    <span class="text-sm font-medium text-slate-600">Có lộ trình học lâu dài, lấy chứng chỉ quốc tế</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="text-center pt-4">
                <button type="submit" class="w-full md:w-auto px-16 py-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-black uppercase tracking-widest shadow-xl shadow-rose-600/30 transition-all hover:-translate-y-1 flex justify-center items-center gap-3 mx-auto">
                    Lưu Hồ Sơ Khảo Sát
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </button>
            </div>

        </form>
    </div>
</main>

<script>
    // Logic nhỏ: Nếu gõ text vào ô "Khác", tự động check vào checkbox "Khác"
    const sourceOtherInput = document.querySelector('input[name="source_other_detail"]');
    const sourceOtherCheck = document.getElementById('source_other_check');
    
    if (sourceOtherInput && sourceOtherCheck) {
        sourceOtherInput.addEventListener('input', function() {
            if(this.value.trim().length > 0) {
                sourceOtherCheck.checked = true;
            } else {
                sourceOtherCheck.checked = false;
            }
        });
    }
</script>