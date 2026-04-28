
<main class="py-12 md:py-16 font-jakarta">
    <div class="container mx-auto px-4 max-w-7xl">
        
        <div class="mb-12 text-center" data-aos="fade-down">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4">Hồ Sơ <span class="text-rose-600">Ứng Tuyển</span></h1>
            <p class="text-slate-500 font-medium">Hoàn thiện thông tin dưới đây để gia nhập đội ngũ chuyên gia tại Nhuệ Minh Edu</p>
        </div>

        <form action="save_application.php" method="POST" enctype="multipart/form-data">
            <div class="grid lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-8 space-y-8">
                    
                    <div class="form-card p-8 md:p-10 shadow-xl shadow-slate-200/50">
                        <h2 class="section-title">1. Thông tin cá nhân</h2>
                        
                        <div class="grid md:grid-cols-2 gap-5 mb-5">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-2">Họ và tên *</label>
                                <input type="text" name="full_name" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-2">Ngày sinh *</label>
                                <input type="date" name="dob" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                            </div>
                        </div>

                        <div class="space-y-5 mb-6">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-2">Số điện thoại *</label>
                                <input type="tel" inputmode="numeric" pattern="[0-9]*" name="phone" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-2">Email *</label>
                                <input type="email" name="email" required class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                            </div>
                        </div>

                        <div class="space-y-1.5 mb-4 border-t border-slate-100 pt-6">
                            <label class="text-xs font-black text-slate-400 uppercase ml-2">Địa chỉ hiện tại</label>
                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <select id="province" name="province" class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold appearance-none cursor-pointer">
                                    <option value="" disabled selected>Chọn Tỉnh/Thành</option>
                                    </select>
                                <select id="district" name="district" disabled class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold appearance-none cursor-not-allowed">
                                    <option value="">Chọn Loại đơn vị</option>
                                </select>
                                <select id="ward" name="ward" disabled class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold appearance-none cursor-not-allowed">
                                    <option value="">Chọn Phường/Xã</option>
                                </select>
                            </div>
                            <input type="text" name="address_detail" placeholder="Số nhà, tên đường, tổ/thôn..." class="w-full px-5 py-3 rounded-2xl bg-slate-50 outline-none focus-lime font-bold">
                        </div>
                    </div>

                    <div class="form-card p-8 md:p-10 shadow-xl shadow-slate-200/50">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="section-title mb-0">2. Kinh nghiệm làm việc</h2>
                            <button type="button" onclick="addExperience()" class="btn-add"><i class="fa-solid fa-plus mr-1"></i> Thêm KN</button>
                        </div>
                        <div id="experience-container">
                            <div class="repeater-box">
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
                            </div>
                        </div>
                    </div>

                <div class="form-card p-8 md:p-10 shadow-xl shadow-slate-200/50">
                    <h2 class="section-title">3. Kỹ năng liên quan</h2>
                    
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                            <h3 class="sub-title text-rose-600"><i class="fa-solid fa-laptop-code mr-2"></i>Kỹ năng chuyên môn</h3>
                            <button type="button" onclick="addProSkill()" class="text-xs font-bold text-lime-600 bg-lime-100 px-3 py-1.5 rounded-lg hover:bg-lime-200 transition-colors"><i class="fa-solid fa-plus mr-1"></i> Thêm 1 dòng</button>
                        </div>
                        <div id="pro-skill-container" class="space-y-3">
                            <div class="relative group">
                                <input type="text" name="skill_pro[]" placeholder="VD: Java Spring Boot..." class="w-full px-5 py-3.5 rounded-xl bg-slate-50 border border-transparent outline-none focus-lime font-bold text-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                            <h3 class="sub-title text-emerald-600"><i class="fa-solid fa-users mr-2"></i>Kỹ năng khác</h3>
                            <button type="button" onclick="addOtherSkill()" class="text-xs font-bold text-lime-600 bg-lime-100 px-3 py-1.5 rounded-lg hover:bg-lime-200 transition-colors"><i class="fa-solid fa-plus mr-1"></i> Thêm 1 dòng</button>
                        </div>
                        <div id="other-skill-container" class="space-y-3">
                            <div class="relative group">
                                <input type="text" name="skill_other[]" placeholder="VD: Giao tiếp tiếng Anh, Kỹ năng sư phạm..." class="w-full px-5 py-3.5 rounded-xl bg-slate-50 border border-transparent outline-none focus-lime font-bold text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="form-card p-8 md:p-10 shadow-xl shadow-slate-200/50">
                        <h2 class="section-title">4. Giới thiệu bản thân</h2>
                        <textarea name="bio" required placeholder="Nêu bật điểm mạnh, kỹ năng cốt lõi và mục tiêu nghề nghiệp của bạn..." class="w-full px-5 py-4 rounded-2xl bg-slate-50 outline-none focus-lime font-bold h-32 resize-none"></textarea>
                    </div>

                </div>

                <div class="lg:col-span-4 space-y-8">
                    <div class="form-card p-8 shadow-xl border-t-8 border-rose-600">
                        <h3 class="text-xl font-black text-slate-800 mb-6 uppercase tracking-tight">Chi tiết công việc</h3>
                        
                        <div class="space-y-6">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-1">Thời gian có thể bắt đầu *</label>
                                <input type="date" name="start_date" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border-none outline-none focus-lime font-bold">
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-1">Mức lương mong muốn *</label>
                                <div class="relative">
                                    <input type="text" id="salary_input" name="salary" required placeholder="15,000,000" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-none outline-none focus-lime font-bold pr-14">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 font-black text-slate-400 text-xs">VNĐ</span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-slate-400 uppercase ml-1">Tải lên CV (PDF/DOC) *</label>
                                <div class="group relative w-full h-32 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center hover:border-lime-400 transition-colors cursor-pointer bg-slate-50">
                                    <input type="file" name="cv_file" required class="absolute inset-0 opacity-0 cursor-pointer">
                                    <div class="text-center">
                                        <i class="fa-solid fa-file-pdf text-2xl text-slate-300 group-hover:text-rose-500 transition-colors"></i>
                                        <p class="text-[10px] font-black text-slate-400 mt-2">CHỌN FILE HỒ SƠ</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full mt-10 bg-rose-600 hover:bg-rose-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-rose-600/20 transition-all hover:-translate-y-1 uppercase tracking-widest text-sm flex justify-center items-center gap-3">
                            Gửi hồ sơ ứng tuyển
                            <div class="w-2 h-2 rounded-full bg-lime-400"></div>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
