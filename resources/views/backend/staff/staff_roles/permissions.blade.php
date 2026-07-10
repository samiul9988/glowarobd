<div class="row">
    <div class="col">
        <div class="mb-3">
            <label>Permissions<span>*</span></label>
            <div class="col-sm-12">
                <div class="mb-2 card-wrapper border rounded-3 checkbox-checked">
                    <h6 class="sub-title">users:</h6>
                    <div class="form-check-size rtl-input">
                        <label class="d-block" for="all-users">
                            <input type="checkbox" class="checkbox_animated select-all-permission select-all-for-users"
                                id="all-users" value="users">All
                        </label>
                        <label class="d-block" for="user.index" data-action="index" data-module="users">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_users module_users_index" value="user.index"
                                id="user.index">index
                        </label>
                        <label class="d-block" for="user.create" data-action="create" data-module="users">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_users module_users_create" value="user.create"
                                id="user.create">create
                        </label>
                        <label class="d-block" for="user.edit" data-action="edit" data-module="users">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_users module_users_edit" value="user.edit"
                                id="user.edit">edit
                        </label>
                        <label class="d-block" for="user.destroy" data-action="trash" data-module="users">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_users module_users_trash" value="user.destroy"
                                id="user.destroy">trash
                        </label>
                        <label class="d-block" for="user.restore" data-action="restore" data-module="users">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_users module_users_restore" value="user.restore"
                                id="user.restore">restore
                        </label>
                        <label class="d-block" for="user.forceDelete" data-action="delete" data-module="users">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_users module_users_delete" value="user.forceDelete"
                                id="user.forceDelete">delete
                        </label>
                    </div>
                </div>
                <div class="mb-2 card-wrapper border rounded-3 checkbox-checked">
                    <h6 class="sub-title">roles:</h6>
                    <div class="form-check-size rtl-input">
                        <label class="d-block" for="all-roles">
                            <input type="checkbox" class="checkbox_animated select-all-permission select-all-for-roles"
                                id="all-roles" value="roles">All
                        </label>
                        <label class="d-block" for="role.index" data-action="index" data-module="roles">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_roles module_roles_index" value="role.index"
                                id="role.index">index
                        </label>
                        <label class="d-block" for="role.create" data-action="create" data-module="roles">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_roles module_roles_create" value="role.create"
                                id="role.create">create
                        </label>
                        <label class="d-block" for="role.edit" data-action="edit" data-module="roles">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_roles module_roles_edit" value="role.edit"
                                id="role.edit">edit
                        </label>
                        <label class="d-block" for="role.destroy" data-action="delete" data-module="roles">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_roles module_roles_delete" value="role.destroy"
                                id="role.destroy">delete
                        </label>
                    </div>
                </div>
                <div class="mb-2 card-wrapper border rounded-3 checkbox-checked">
                    <h6 class="sub-title">attachments:</h6>
                    <div class="form-check-size rtl-input">
                        <label class="d-block" for="all-attachments">
                            <input type="checkbox"
                                class="checkbox_animated select-all-permission select-all-for-attachments"
                                id="all-attachments" value="attachments">All
                        </label>
                        <label class="d-block" for="attachment.index" data-action="index" data-module="attachments">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_attachments module_attachments_index"
                                value="attachment.index" id="attachment.index">index
                        </label>
                        <label class="d-block" for="attachment.create" data-action="create"
                            data-module="attachments">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_attachments module_attachments_create"
                                value="attachment.create" id="attachment.create">create
                        </label>
                        <label class="d-block" for="attachment.destroy" data-action="delete"
                            data-module="attachments">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_attachments module_attachments_delete"
                                value="attachment.destroy" id="attachment.destroy">delete
                        </label>
                    </div>
                </div>
                <div class="mb-2 card-wrapper border rounded-3 checkbox-checked">
                    <h6 class="sub-title">categories:</h6>
                    <div class="form-check-size rtl-input">
                        <label class="d-block" for="all-categories">
                            <input type="checkbox"
                                class="checkbox_animated select-all-permission select-all-for-categories"
                                id="all-categories" value="categories">All
                        </label>
                        <label class="d-block" for="category.index" data-action="index" data-module="categories">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_categories module_categories_index"
                                value="category.index" id="category.index">index
                        </label>
                        <label class="d-block" for="category.create" data-action="create" data-module="categories">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_categories module_categories_create"
                                value="category.create" id="category.create">create
                        </label>
                        <label class="d-block" for="category.edit" data-action="edit" data-module="categories">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_categories module_categories_edit"
                                value="category.edit" id="category.edit">edit
                        </label>
                        <label class="d-block" for="category.destroy" data-action="delete" data-module="categories">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_categories module_categories_delete"
                                value="category.destroy" id="category.destroy">delete
                        </label>
                    </div>
                </div>
                <div class="mb-2 card-wrapper border rounded-3 checkbox-checked">
                    <h6 class="sub-title">tags:</h6>
                    <div class="form-check-size rtl-input">
                        <label class="d-block" for="all-tags">
                            <input type="checkbox" class="checkbox_animated select-all-permission select-all-for-tags"
                                id="all-tags" value="tags">All
                        </label>
                        <label class="d-block" for="tag.index" data-action="index" data-module="tags">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_tags module_tags_index" value="tag.index"
                                id="tag.index">index
                        </label>
                        <label class="d-block" for="tag.create" data-action="create" data-module="tags">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_tags module_tags_create" value="tag.create"
                                id="tag.create">create
                        </label>
                        <label class="d-block" for="tag.edit" data-action="edit" data-module="tags">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_tags module_tags_edit" value="tag.edit"
                                id="tag.edit">edit
                        </label>
                        <label class="d-block" for="tag.destroy" data-action="trash" data-module="tags">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_tags module_tags_trash" value="tag.destroy"
                                id="tag.destroy">trash
                        </label>
                        <label class="d-block" for="tag.restore" data-action="restore" data-module="tags">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_tags module_tags_restore" value="tag.restore"
                                id="tag.restore">restore
                        </label>
                        <label class="d-block" for="tag.forceDelete" data-action="delete" data-module="tags">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_tags module_tags_delete" value="tag.forceDelete"
                                id="tag.forceDelete">delete
                        </label>
                    </div>
                </div>
                <div class="mb-2 card-wrapper border rounded-3 checkbox-checked">
                    <h6 class="sub-title">blogs:</h6>
                    <div class="form-check-size rtl-input">
                        <label class="d-block" for="all-blogs">
                            <input type="checkbox"
                                class="checkbox_animated select-all-permission select-all-for-blogs" id="all-blogs"
                                value="blogs">All
                        </label>
                        <label class="d-block" for="blog.index" data-action="index" data-module="blogs">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_blogs module_blogs_index" value="blog.index"
                                id="blog.index">index
                        </label>
                        <label class="d-block" for="blog.create" data-action="create" data-module="blogs">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_blogs module_blogs_create" value="blog.create"
                                id="blog.create">create
                        </label>
                        <label class="d-block" for="blog.edit" data-action="edit" data-module="blogs">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_blogs module_blogs_edit" value="blog.edit"
                                id="blog.edit">edit
                        </label>
                        <label class="d-block" for="blog.destroy" data-action="trash" data-module="blogs">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_blogs module_blogs_trash" value="blog.destroy"
                                id="blog.destroy">trash
                        </label>
                        <label class="d-block" for="blog.restore" data-action="restore" data-module="blogs">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_blogs module_blogs_restore" value="blog.restore"
                                id="blog.restore">restore
                        </label>
                        <label class="d-block" for="blog.forceDelete" data-action="delete" data-module="blogs">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_blogs module_blogs_delete" value="blog.forceDelete"
                                id="blog.forceDelete">delete
                        </label>
                    </div>
                </div>
                <div class="mb-2 card-wrapper border rounded-3 checkbox-checked">
                    <h6 class="sub-title">pages:</h6>
                    <div class="form-check-size rtl-input">
                        <label class="d-block" for="all-pages">
                            <input type="checkbox"
                                class="checkbox_animated select-all-permission select-all-for-pages" id="all-pages"
                                value="pages">All
                        </label>
                        <label class="d-block" for="page.index" data-action="index" data-module="pages">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_pages module_pages_index" value="page.index"
                                id="page.index">index
                        </label>
                        <label class="d-block" for="page.create" data-action="create" data-module="pages">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_pages module_pages_create" value="page.create"
                                id="page.create">create
                        </label>
                        <label class="d-block" for="page.edit" data-action="edit" data-module="pages">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_pages module_pages_edit" value="page.edit"
                                id="page.edit">edit
                        </label>
                        <label class="d-block" for="page.destroy" data-action="trash" data-module="pages">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_pages module_pages_trash" value="page.destroy"
                                id="page.destroy">trash
                        </label>
                        <label class="d-block" for="page.restore" data-action="restore" data-module="pages">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_pages module_pages_restore" value="page.restore"
                                id="page.restore">restore
                        </label>
                        <label class="d-block" for="page.forceDelete" data-action="delete" data-module="pages">
                            <input type="checkbox" name="permissions[]"
                                class="checkbox_animated module_pages module_pages_delete" value="page.forceDelete"
                                id="page.forceDelete">delete
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
