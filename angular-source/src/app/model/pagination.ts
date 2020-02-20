import { PaginationMode } from "../enum/pagination-mode";

export class Pagination {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	// is pagination active
	public active: boolean = false;

	// pagination: current page
	public currentPage: number = 1;

	// pagination: current letter
	public currentLetter: string = 'A';

	// pagination mode (letters, numbers, load_more etc)
	public mode: string = PaginationMode.Numbers;

	public paginationSelectors: object = {};

	// total pages/max page number
	public totalPages: number = 1;

	// page number shifter (if necessary)
	public pageShift: number = 0;

	// min package value
	public minPackage: number = 1;

	// max package value
	public maxPackage: number = 10;

	// current selected package
	public currentPackage: number = 1;

	// number of post/images in one package
	public packageSize: number = 100;

}