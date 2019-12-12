import {Component, EventEmitter, Input, Output, OnInit, OnChanges, SimpleChanges} from '@angular/core';
import { PaginationMode } from "../../../../enum/pagination-mode";
import { Pagination } from "../../../../model/pagination";

@Component({
	selector: 'app-paginator',
	templateUrl: './paginator.component.html'
})
export class PaginatorComponent implements OnInit, OnChanges {

	@Input() pagination: Pagination;

	@Output() onPaginate = new EventEmitter<Pagination>();

	public pages = [];

	public currentPage;
	public firstPage;
	public lastPage;

	public previousPage;
	public nextPage;

	public loadMore = false;

	public alphabet = Array.from(Array(26), (e, i) => String.fromCharCode(i + 97));

	constructor() { }

	ngOnInit() {
		this.createPaginationData();
	}

	/**
	 * Rework pagination data on pagination settings change event;
	 *
	 * @param changes
	 */
	ngOnChanges(changes: SimpleChanges): void {
		if (typeof changes.pagination !== undefined && changes.pagination.previousValue !== changes.pagination.currentValue) {
			this.createPaginationData();
		}
	}

	/**
	 * Select page action
	 * @param pageValue
	 */
	public selectPage(pageValue: any) {
		if (this.pagination.mode === PaginationMode.Letters) {
			if (this.pagination.currentLetter === pageValue) {
				return;
			}

			this.pagination.currentLetter = pageValue;
		} else {
			this.pagination.currentPage = pageValue;
		}

		this.pagination.currentPage = pageValue;
		this.onPaginate.next(this.pagination);
		this.createPaginationData();
	}

	public toggleLoadMore() {
		this.pagination.mode = PaginationMode.LoadMore;
		this.onPaginate.next(this.pagination);
	}

	protected resetPagination() {
		this.pages = [];

		this.currentPage = null;
		this.firstPage = null;
		this.lastPage = null;

		this.previousPage = null;
		this.nextPage = null;

		this.loadMore = false;
	}

	protected createPaginationData() : void {
		this.resetPagination();

		switch (this.pagination.mode) {
			case PaginationMode.Letters:
				for (let index in this.alphabet) {
					this.pages.push(this.alphabet[index].toUpperCase());
				}

				this.pages.push('Symbol');
				this.currentPage = this.pagination.currentLetter.toUpperCase();
				break;

			case PaginationMode.Numbers:
				for (let x = 1; x <= this.pagination.totalPages; x++) {
					this.pages.push(x);
				}

				this.currentPage = this.pagination.currentPage;
				break;

			case PaginationMode.LoadMore:
				this.loadMore = true;
				return;
		}

		let previousPageIndex = null;
		let pageFound = false;

		for (let page of this.pages) {
			if (pageFound) {
				this.nextPage = page;
				break;
			}

			if (page === this.currentPage) {
				pageFound = true;
				this.previousPage = (!previousPageIndex) ? this.currentPage : previousPageIndex;
			}

			previousPageIndex = page;
		}

		if (this.nextPage === null) {
			this.nextPage = (this.pagination.mode === PaginationMode.Letters) ? '0' : this.pagination.totalPages;
		}
	}

}
