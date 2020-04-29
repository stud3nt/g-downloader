import { Component, OnInit } from '@angular/core';
import { CategoriesService } from "../../../service/categories-service";
import { Category } from "../../../model/category";
import { JsonResponse } from "../../../model/json-response";
import { ContentHeaderDataService } from "../../../service/data/content-header-data.service";
import { ModalService } from "../../../service/modal.service";
import { ModalType } from "../../../enum/modal-type";
import { ToastrDataService } from "../../../service/data/toastr-data.service";

@Component({
	selector: 'app-categories',
	templateUrl: './categories.component.html',
	styleUrls: ['./categories.component.scss']
})
export class CategoriesComponent implements OnInit {

	public _categories: Category[] = [];

	public _operatedCategory: Category = new Category();

	public _editModalTitle: string = '';
	public _editModalId: string = 'categories-editor-modal';
	public _deleteModalId: string = 'categories-delete-modal';

	public ModalType = ModalType;

	constructor(
		private headerData: ContentHeaderDataService,
		private categoriesService: CategoriesService,
		private modalService: ModalService,
		private toastr: ToastrDataService
	) { }

	ngOnInit() {
		this.setHeaderData();
		this.getList();
	}

	public editCategory(category: Category = null) {
		if (category)
			this._operatedCategory = category;
		else
			this._operatedCategory = new Category();

		this._editModalTitle = ((category) ? 'Edit' : 'Create') + ' category';
		this.modalService.selectModal(this._editModalId).open();
	}

	public saveEditedCategory() {
		this.categoriesService.saveCategory(this._operatedCategory).subscribe((response: JsonResponse) => {
			this.getList();
		});
	}

	public deleteCategory(category: Category) {
		this._operatedCategory = category;
		this.modalService.selectModal(this._editModalId).open();
	}

	public delete() {
		this.categoriesService.deleteCategory(this._operatedCategory).subscribe((response: JsonResponse) => {
			this.getList();
			this._operatedCategory = new Category();
		}, (error) => {
			this._operatedCategory = new Category();
			this.toastr.addError(error);
		}, );
	}

	public clearOperatedCategory(): void {
		this._operatedCategory = new Category();
	}

	private getList(): void {
		this.categoriesService.getCategories().subscribe((response: JsonResponse) => {
			if (response.success()) {
				this._categories = [];

				if (response.data) {
					for (let row of response.data) {
						this._categories.push(
							new Category(row)
						);
					}
				}
			}
		});
	}

	private setHeaderData(): void {
		this.headerData.setElement('title1', 'Categories');
		this.headerData.setElement('title2', 'Viewing and editing categories list');
		this.headerData.clearBreadcrumbs();
	}

}
