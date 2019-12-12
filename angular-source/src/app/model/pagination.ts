import { PaginationMode } from "../enum/pagination-mode";

export class Pagination {

	constructor(obj?: any) {
		Object.assign(this, obj);
	}

	// is pagination active
	active: boolean = false;

	// pagination: current page
	currentPage: number = 1;

	// pagination: current letter
	currentLetter: string = 'A';

	// pagination mode (letters, numbers, load_more etc)
	mode:  string = PaginationMode.Numbers;

	// total pages/max page number
	totalPages: number = 1;

	// page number shifter (if necessary)
	pageShift: number = 0;

}